<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    public function index()
    {
        return $this->render('app.html.twig');
    }

    public function data()
    {
        $fileName = $this->getParameter('kernel.project_dir') . '/data/data.json';
        if (!is_file($fileName)) {
            file_put_contents($fileName, json_encode($this->getDefaultData()));
        }

        $data = json_decode(file_get_contents($fileName), true);

        return new JsonResponse($data);
    }

    private function getDefaultData()
    {
        $collectionA = [
            'id' => 'colA',
            'items' => [
                [
                    'title' => 'Artikel A',
                    'lead' => 'Bla bla bla',
                ],
                [
                    'title' => 'Artikel B',
                    'lead' => 'Da steht was interessantes',
                ],
                [
                    'title' => 'Artikel C',
                    'lead' => 'Judihui, der beste Artikel',
                ],
            ]
        ];

        $collectionB = [
            'id' => 'colB',
            'items' => [
                [
                    'title' => 'Artikel X',
                    'lead' => 'Bla bla bla',
                ],
                [
                    'title' => 'Artikel Y',
                    'lead' => 'Da steht was interessantes',
                ],
            ]
        ];

        return [$collectionA, $collectionB];
    }
}