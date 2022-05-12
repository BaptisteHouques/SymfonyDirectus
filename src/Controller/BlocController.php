<?php

namespace App\Controller;

use App\Service\DirectusRestApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BlocController extends AbstractController
{
    private DirectusRestApi $directusRestApi;

    public function __construct(DirectusRestApi $directusRestApi) {
        $this->directusRestApi = $directusRestApi;
    }

    /**
     * @Route("/blocInformation", name="blocInformation")
     * @return Response
     */
    public function index(): Response
    {
        $urlGetBloc = $_ENV['DIRECTUS_API'] . '/items/bloc_information?fields=*,Erreur_Bloc.*';
        $urlGetBanniere = $_ENV['DIRECTUS_API'] . '/items/banniere';

        $bloc_informations = $this->directusRestApi->callDirectusApi($urlGetBloc);
        $banniere = $this->directusRestApi->callDirectusApi($urlGetBanniere);

        if($bloc_informations && $banniere)
            return $this->render('blocInformationView.html.twig', ['bloc_informations' => json_decode($bloc_informations)->data, 'banniere' => $banniere,'directus_api' => $_ENV['DIRECTUS_API']]);
        else
            return new Response("Erreur dans la réponse", Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/apiUpdateBlocError/{idBloc}/{errorBloc}", name="apiUpdateBlocError")
     * @param Request $request
     * @param $idBloc
     * @param $errorBloc
     * @return string
     */
    public function apiUpdateBlocError (Request $request, $idBloc, $errorBloc)
    {
        $url = $_ENV['DIRECTUS_API'] . '/items/bloc_information/' . $idBloc;
        $token = 't';
        $body = json_encode(["Erreur_Bloc" => $errorBloc]);
        $headers = ["Authorization" => "Bearer $token", "Content-Type" => "application/json"];

        $response = $this->directusRestApi->callDirectusApi($url, 'PATCH', $body, $headers);

        if ($response)
            return $response;
        else
            return new Response("Erreur dans la réponse", Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/apiGetErreurService", name="apiGetErreurService")
     * @return false|string
     */
    public function apiGetErreurService ()
    {
        $url = $_ENV['DIRECTUS_API'] . '/items/erreur_service/';
        $response = $this->directusRestApi->callDirectusApi($url);
        if ($response)
            return $response;
        else
            return false;
    }

    /**
     * @Route("/updateBlocError{idBloc}", name="updateBlocError")
     * @param $idBloc
     * @return Response
     */
    public function updateBlocError ($idBloc): Response
    {
        $errorBloc = null;
        if (isset($_POST['valueSelected']))
            $errorBloc = $_POST['valueSelected'];

        $bloc = $this->apiUpdateBlocError(new Request(), $idBloc, $errorBloc);
        if ($bloc) {
            $bloc = json_decode($bloc)->data;
            ($bloc->ImageArrierePlan != null) ? $image = $_ENV['DIRECTUS_API'] . '/assets/' . $bloc->ImageArrierePlan : $image = 'https://www.projetcartylion.fr/app/uploads/2020/08/Placeholder.png';

            if ($errorBloc) {
                return $this->render('components/_cards.blocInformation.error.html.twig', ['id' => $bloc->id, 'titre' => $bloc->Titre, 'image' => $image, 'errorType' => 'tempType', 'errorDesc' => 'tempDesc']);
            }
            else {
                return $this->render('components/_cards.blocInformation.html.twig', ['id' => $bloc->id, 'titre' => $bloc->Titre, 'desc' => $bloc->Description, 'lien' => $bloc->Lien, 'image' => $image]);
            }
        }
        else
            return new Response("Erreur dans la réponse", Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/showListError/{idBloc}/{titreBloc}", name="showListError")
     * @param $idBloc
     * @param $titreBloc
     * @return Response
     */
    public function showListError ($idBloc, $titreBloc): Response
    {
        $listErrors = $this->apiGetErreurService();
        if ($listErrors) {
            $listErrors = json_decode($listErrors)->data;
            return $this->render('components/_selectErreurService.html.twig', ['list' => $listErrors, 'id' => $idBloc,'titre' => $titreBloc]);
        }
        else
            return new Response("Erreur dans la réponse", Response::HTTP_NOT_FOUND);
    }
}