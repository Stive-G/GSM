<?php
namespace App\Controller\Admin;

use App\Service\LogService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LogsController extends AbstractController
{
    public function __construct(private readonly LogService $logs) {}

    #[Route('/admin/logs', name: 'admin_logs', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $type  = $request->query->get('type');
        $route = $request->query->get('route');
        $email = $request->query->get('email');

        $filter = [];
        if ($type)  $filter['type'] = $type;
        if ($route) $filter['route'] = $route;
        if ($email) $filter['user.email'] = $email;

        $page  = max(1, (int)$request->query->get('page', 1));
        $limit = 25;
        $skip  = ($page - 1) * $limit;

        $total = $this->logs->count($filter);
        $items = $this->logs->find($filter, [
            'sort'  => ['ts' => -1],
            'limit' => $limit,
            'skip'  => $skip,
        ]);

        return $this->render('admin/logs.html.twig', [
            'items'  => $items,
            'total'  => $total,
            'page'   => $page,
            'pages'  => (int) ceil($total / $limit),
            'filter' => ['type' => $type, 'route' => $route, 'email' => $email],
        ]);
    }
}
