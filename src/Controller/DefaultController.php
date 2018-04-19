<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function index()
    {
        return $this->render('app.html.twig');
    }

    public function data()
    {
        return new JsonResponse($this->getData());
    }

    public function publish(Request $request)
    {
        $commands = json_decode($request->getContent(), true);
        $data = $this->getData();
        foreach ($commands as $command) {
            $data = $this->applyCommand($data, $command);
        }

        $fileName = $this->getParameter('kernel.project_dir') . '/data/data.json';
        file_put_contents($fileName, json_encode($data));

        return new JsonResponse($data);
    }

    private function getData()
    {
        $fileName = $this->getParameter('kernel.project_dir') . '/data/data.json';
        if (!is_file($fileName)) {
            file_put_contents($fileName, json_encode($this->getDefaultData()));
        }

        return json_decode(file_get_contents($fileName), true);
    }

    private function applyCommand($data, $command)
    {
        if ($command['type'] === 'reorder') {
            foreach ($data as $colId => $collection) {
                if ($command['collection'] == $collection['id']) {
                    // find index of item to reorder
                    $reorderItems = array_values(array_filter($collection['items'], function ($item) use ($command) {
                        return $item['id'] == $command['item'];
                    }));

                    $out = array_splice($data[$colId]['items'], array_search($reorderItems[0], $collection['items']), 1);
                    array_splice($data[$colId]['items'], $command['position'], 0, $out);
                }
            }

        }
        return $data;
    }

    private function getDefaultData()
    {
        $collectionA = [
            'id' => 'urn:collection:1',
            'items' => [
                [
                    'id' => 'urn:article:1',
                    'title' => 'Artikel A',
                    'lead' => 'Bla bla bla',
                ],
                [
                    'id' => 'urn:article:2',
                    'title' => 'Artikel B',
                    'lead' => 'Da steht was interessantes',
                ],
                [
                    'id' => 'urn:article:3',
                    'title' => 'Artikel C',
                    'lead' => 'Judihui, der beste Artikel',
                ],
            ]
        ];

        $collectionB = [
            'id' => 'urn:collection:2',
            'items' => [
                [
                    'id' => 'urn:article:10',
                    'title' => 'Artikel X',
                    'lead' => 'Bla bla bla',
                ],
                [
                    'id' => 'urn:article:11',
                    'title' => 'Artikel Y',
                    'lead' => 'Da steht was interessantes',
                ],
            ]
        ];

        return [$collectionA, $collectionB];
    }
}