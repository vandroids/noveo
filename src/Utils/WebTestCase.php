<?php

namespace App\Utils;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Faker\Factory;
use Faker\Generator;
use App\Serializer\Normalizer\DateTimeNormalizer;
use App\Serializer\Normalizer\CollectionNormalizer;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    /** @var Generator */
    protected static $faker;

    public static function setUpBeforeClass()
    {
        self::runCommand('doctrine:database:drop --force');
        self::runCommand('doctrine:database:create');
        self::runCommand('doctrine:schema:update --force');
        self::runCommand('doctrine:fixtures:load --no-interaction');

        self::$faker = Factory::create();
    }

    protected static function runCommand(string $command)
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->run(new StringInput($command), new NullOutput());
    }

    protected function serialize($data)
    {
        $encoders = [new JsonEncoder()];
        $objectNormalizer = new ObjectNormalizer(new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader())));
        $objectNormalizer->setCircularReferenceLimit(1);
        $objectNormalizer->setCircularReferenceHandler(function ($object) {
            if (method_exists($object, 'getId')) {
                return $object->getId();
            }
        });
        $normalizers = [new DateTimeNormalizer(), new CollectionNormalizer(), $objectNormalizer];
        $serializer = new Serializer($normalizers, $encoders);
        $data = $serializer->serialize($data, 'json', [
            'json_encode_options' => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
        ]);

        return $data;
    }
}
