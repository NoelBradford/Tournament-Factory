<?php

namespace App\Controller;

use App\Entity\TFTournament;
use App\Services\Enum\TournamentTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class TournamentController extends Controller
{
    /**
     * @var EntityManagerInterface $entityManger
     */
    private $entityManger;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManger = $entityManager;
    }

    /**
     * @Route("/tournament", name="my_tournament")
     */
    public function index()
    {
        /**
         * @var TFTournament $tournaments
         */
        $tournaments = $this->entityManger->getRepository('App\Entity\TFTournament')->findAll();

        return $this->render('tournament/index.html.twig', [
            'controller_name' => 'TournamentController',
            'tournaments' => $tournaments
        ]);
    }

    /**
     * @Route("/tournament/create", name="chosen-type")
     */
    public function chooseTournament() {
        return $this->render('tournament/chosen-type.html.twig');
    }

    /**
     * @Route("/tournament/create/{type}", name="create-tournament", requirements={"\s"})
     */
    public function createTournament (Request $request, string $type) {
        if(!TournamentTypeEnum::getTypeName($type)){
            throw new NotFoundHttpException('Ce type de tournoi n\'existe pas');
        }

        $tournament = new TFTournament($type);
        $form = $this->createForm('App\Form\Type\TFTournamentType', $tournament);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $tournament->setOwner($this->getUser()->getTFUser());

            $this->entityManger->persist($tournament);
            $this->entityManger->flush();

            $this->addFlash('success', 'tournament.new.message');

            return $this->redirectToRoute('chosen-type');
        }

        return $this->render('tournament/new-tournament.html.twig', [
            'form' => $form->createView(),
            'type' => $type
        ]);
    }

    /**
     * @Route("/tournament/remove", name="remove_tournament")
     */
    public function removeTournament (Request $request) {
        $index = $request->get('tournament-id');
        $tournament = $this->entityManger->getRepository('App:TFTournament')->find($index);

        if($tournament) {
            $this->entityManger->remove($tournament);
            $this->entityManger->flush();
            $this->addFlash('success', 'tournament.remove.message');
            return $this->redirectToRoute('my_tournament');
        }

        $this->addFlash('warning', 'tournament.remove.message');

        return $this->redirectToRoute('my_tournament');
    }
}
