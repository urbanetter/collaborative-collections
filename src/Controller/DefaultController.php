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
        $data = $this->getData();
        return new JsonResponse($data);
    }

    public function publish(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        $data = $this->getData();
        foreach ($payload['changes'] as $change) {
            $data['collections'] = $this->applyChange($data['collections'], $change);
        }

        $data['version'] = $payload['version'];

        $fileName = $this->getParameter('kernel.project_dir') . '/data/data.json';
        file_put_contents($fileName, json_encode($data));

        return new JsonResponse($data);
    }

    public function version()
    {
        $data = $this->getData();
        return new JsonResponse($data['version']);
    }

    private function getData()
    {
        $fileName = $this->getParameter('kernel.project_dir') . '/data/data.json';
        if (!is_file($fileName)) {
            file_put_contents($fileName, json_encode($this->getDefaultData()));
        }

        return json_decode(file_get_contents($fileName), true);
    }

    private function applyChange($data, $change)
    {
        if ($change['type'] === 'reorder') {
            foreach ($data as $colId => $collection) {
                if ($change['collection'] == $collection['id']) {
                    // find index of item to reorder
                    $reorderItems = array_values(array_filter($collection['items'], function ($item) use ($change) {
                        return $item['id'] == $change['item'];
                    }));

                    $out = array_splice($data[$colId]['items'], array_search($reorderItems[0], $collection['items']), 1);
                    array_splice($data[$colId]['items'], $change['position'], 0, $out);
                }
            }

        }
        return $data;
    }

    private function getDefaultData()
    {
        $collectionA = [
            'id' => 'urn_collection_1',
            'items' => [
                [
                    'id' => 'urn_article_1',
                    'title' => 'Artikel A',
                    'lead' => 'Bla bla bla',
                ],
                [
                    'id' => 'urn_article_2',
                    'title' => 'Artikel B',
                    'lead' => 'Da steht was interessantes',
                ],
                [
                    'id' => 'urn_article_3',
                    'title' => 'Artikel C',
                    'lead' => 'Judihui, der beste Artikel',
                ],
            ]
        ];

        $collectionB = [
            'id' => 'urn_collection_2',
            'items' => [
                [
                    'id' => 'urn_article_10',
                    'title' => 'Artikel X',
                    'lead' => 'Bla bla bla',
                ],
                [
                    'id' => 'urn_article_11',
                    'title' => 'Artikel Y',
                    'lead' => 'Da steht was interessantes',
                ],
            ]
        ];

        return [
            "version" => [
                "user" => "Initial example data",
                "version" => time(),
            ],
            "collections" => [$collectionA, $collectionB],
        ];
    }
}