<?php

namespace TE\SearchifyBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\AbstractQuery;

class IndexCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('searchify:index')
            ->setDescription('Add all objects to index')
            ->addArgument('entity', InputArgument::REQUIRED, 'Entity to index. Ex: "TE\\TestBundle\\Entity\\Object"')
        ;
    }

    // entity manager
    private $em     = null;

    // database connection
    private $conn   = null;

    // logger
    private $output = null;

    // locales
    private $locales = array();

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get access to services
        $this->output    = $output;
        $this->em        = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->conn      = $this->getContainer()->get('database_connection');
        $this->searchify = $this->getContainer()->get('searchify');
        $this->locales   =
            $this->getContainer()->hasParameter('te.doctrine_behaviors.translatable_listener.accepted_locales')
            ? $this->getContainer()->getParameter('te.doctrine_behaviors.translatable_listener.accepted_locales')
            : array();

        // disable sql logger (avoid memory problems)
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);

        $this->index( $input->getArgument('entity') );
    }

    /**
     * Index all objects of this entity
     *
     * @param string $entity Complete path of the entity to index
     */
    private function index($entity) {

        $this->output->writeln('Index: '.$entity);

        $classMetadata = $this->em->getClassMetadata($entity);
        $tableName     = $classMetadata->getTableName();
        $where         = isset($entity::$whereInIndex) ? $entity::$whereInIndex : '';

        // get total objects to index
        $totalObjects = $this->conn->executeQuery('SELECT count(1) FROM '.$tableName.' as o '
                . ( $where ? 'where '.$where : ''))->fetchColumn();
        $this->output->writeln($totalObjects. ' objects to index');

        // get last id to index. We use the id instead of limit {offset}, {limit} because of sql performance
        $lastId = $this->em
            ->createQuery('SELECT o.id FROM '.$entity.' o order by o.id desc')
            ->setMaxResults(1)
            ->getSingleScalarResult();

        // loop through all objects
        $lastIndexedId = 0;
        $totalIndexed  = 0;
        $MAX           = 1000;

        while ( $lastIndexedId < $lastId ) {

            $select = $entity::$fieldsToIndex;

            // if the entity has translations
            $joinTranslation = '';
            if ( $this->hasTranslations($classMetadata->reflClass, true) ){

                // create joins
                foreach ($this->locales as $locale) {
                    $joinTranslation .= 'LEFT JOIN ' . $tableName .'_translation as tr_'.$locale.'
                        on o.id=tr_'.$locale.'.translatable_id and tr_'.$locale.'.locale="'.$locale.'" ';
                }

                // get fields to get
                $translationEntity = $entity.'Translation';
                foreach ( $translationEntity::$fieldsToIndex as $key => $fields ) {

                    $fieldsInAllLocales = array();
                    foreach ($this->locales as $locale) {
                        foreach ($fields as $field) {
                            $fieldsInAllLocales[] = 'tr_'.$locale.'.'.$field;
                        }
                    }

                    $select[] = "CONCAT_WS(' ', " . join(",", $fieldsInAllLocales) . ') as '.$key;
                }
            }

            $results = $this->conn->executeQuery('SELECT ' . join(', ', $select)
                .' FROM ' . $tableName .' as o ' . $joinTranslation
                .' where o.id > ' . $lastIndexedId .' '
                    .( $where ? ' and '.$where : '' )
                .' order by o.id asc limit '.$MAX)->fetchAll();

            $documents = array();
            foreach ($results as $r ) {
                $documents[] = $entity::getArrayToIndexFromArray($r);
            }

            $this->searchify->addDocuments($documents);

            $totalIndexed += count($documents);
            $lastObject    = array_pop($results);
            $lastIndexedId = $lastObject['id'];

            $results       = null;
            $documents     = null;
            $lastObject    = null;

            $this->output->writeln($totalIndexed. ' objects indexed');
        }

        $this->output->writeln('done');
    }

    /**
     * Checks if entity has translations
     *
     * @param ClassMetadata $classMetadata
     * @param bool          $isRecursive   true to check for parent classes until trait is found
     *
     * @return boolean
     */
    private function hasTranslations(\ReflectionClass $reflClass, $isRecursive = false)
    {
        $isSupported = in_array('TE\DoctrineBehaviorsBundle\Model\Translatable\Translatable', $reflClass->getTraitNames());

        while ($isRecursive and !$isSupported and $reflClass->getParentClass()) {
            $reflClass = $reflClass->getParentClass();
            $isSupported = $this->hasTranslations($reflClass, true);
        }

        return $isSupported;
    }

}