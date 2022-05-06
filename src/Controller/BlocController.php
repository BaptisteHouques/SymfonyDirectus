<?php

namespace App\Controller;

use App\Service\DirectusRestApi;
use http\Client\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlocController extends AbstractController
{
    /**
     * @Route("/blocInformation", name="blocInformation")
     * @param DirectusRestApi $directusRestApi
     * @return Response
     */
    public function index(DirectusRestApi $directusRestApi): Response
    {
        //REST
        $directusGetBloc = $_ENV['DIRECTUS_API'] . '/items/bloc_information?fields=*,Erreur_Bloc.*';
        $directusGetBanniere = $_ENV['DIRECTUS_API'] . '/items/banniere';

        //GRAPHQL
        $directusGetBlocGraphQL = $_ENV['DIRECTUS_API'] . '/graphql';
        $directusGetBlocGraphQLQuery = "query {
            bloc_information {
                Titre
                Description
                Lien
                ImageArrierePlan {
                    id
                }
                Erreur_Bloc {
                    CodeJSON_Erreur
                }
            }
        }";

        //REST CALL
        $bloc_informations = $directusRestApi->getDirectusApi($directusGetBloc);
        $banniere = $directusRestApi->getDirectusApi($directusGetBanniere);

        //GRAPHQL CALL
        //$bloc_informations = $directusRestApi->directusGraphQL($directusGetBlocGraphQL, $directusGetBlocGraphQLQuery);

        dump($bloc_informations);
        if($bloc_informations && $banniere)
            return $this->render('blocInformationView.html.twig', ['bloc_informations' => $bloc_informations, 'banniere' => $banniere,'directus_api' => $_ENV['DIRECTUS_API']]);
        else
            return new Response("Erreur dans la réponse", Response::HTTP_NOT_FOUND);
    }

    /**
     * @Route("/removeBlocError/{idBloc}", name="removeBlocError")
     * @param DirectusRestApi $directusRestApi
     * @param $idBloc
     * @return string|JsonResponse
     */
    public function removeBlocError (DirectusRestApi $directusRestApi, $idBloc) {
        //return new JsonResponse(['test' => 'test']);

        $url = $_ENV['DIRECTUS_API'] . '/items/bloc_information/' . $idBloc;
        $body = ["Erreur_Bloc" => null];

        $response = $directusRestApi->removeRelation($url, $body);
        if ($response)
            return new JsonResponse($response);
        else
            return new JsonResponse("erreur");

    }
}