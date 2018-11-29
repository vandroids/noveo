<?php

namespace App\Controller\API;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use App\Serializer\Normalizer\DateTimeNormalizer;
use App\Serializer\Normalizer\CollectionNormalizer;

class API
{
    protected function json($data, int $status = 200, array $headers = []): JsonResponse
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

        $json = $serializer->serialize($data, 'json', [
            'json_encode_options' => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
        ]);

        return new JsonResponse($json, $status, $headers, true);
    }
}
