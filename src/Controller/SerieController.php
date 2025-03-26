<?php

namespace App\Controller;

use App\Repository\SerieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class SerieController extends AbstractController
{
    #[Route('/series', name: 'series')]
    public function series(SerieRepository $serieRepository, Request $request): Response
    {
        $page = max($request->query->getInt('page', 1), 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
    
        // Récupération du filter "status"
        $status = $request->query->get('status', null);
    
        // Construction du QueryBuilder
        $qb = $serieRepository->createQueryBuilder('s')
            ->setFirstResult($offset)
            ->setMaxResults($limit);
    
        if ($status) {
            $qb->where('s.status = :status')
               ->setParameter('status', $status);
        }
    
        // Récupérer les séries paginées
        $series = $qb->getQuery()->getResult();
    
        // Récupérer nombre total de séries
        $total = $serieRepository->createQueryBuilder('s')
            ->select('COUNT(s.id)');
    
        if ($status) {
            $total->where('s.status = :status')
                  ->setParameter('status', $status);
        }
    
        $total = $total->getQuery()->getSingleScalarResult();
        $totalPages = (int) ceil($total / $limit);
    
        // Transmettre "status" pour la pagination
        return $this->render('serie/series.html.twig', [
            'series'      => $series,
            'totalPages'  => $totalPages,
            'currentPage' => $page,
            'status'      => $status,
        ]);
    }
}
