<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\HomeService;

class HomeController extends Controller
{
    protected $homeService;

    // Injection du service HomeService
    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
    }

    // Action pour récupérer les informations de compte et les commandes
    public function getCompteInfo($id)
    {
        return $this->homeService->getCompteInfo($id);
    }

    // Action pour récupérer les produits par sous-catégorie
    public function getProduitsBySousCategories($parametre)
    {
        return $this->homeService->getProduitsBySousCategories($parametre);
    }

    // Action pour récupérer les produits par catégorie
    public function getProduitsbycategories($value)
    {
        return $this->homeService->getProduitsbycategories($value);
    }

    // Action pour récupérer les nouveaux produits
    public function getNouveauteProduits()
    {
        return $this->homeService->getNouveauteProduits();
    }

    // Action pour le Mega Menu
    public function MegaMenu()
    {
        return $this->homeService->MegaMenu();
    }

    // Action pour l'index de la page d'accueil
    public function index()
    {
        return $this->homeService->index();
    }

    // Confirmation de commande par mail
    public function confirmationMail(Request $request)
    {
        return $this->homeService->confirmationMail($request);
    }

    // Envoi d'email pour devis
    public function sendEmailsDevis(Request $request)
    {
        return $this->homeService->sendEmailsDevis($request);
    }

    // Modification du statut de la commande
    public function changesstatOrder($id, $status)
    {
        return $this->homeService->changesstatOrder($id, $status);
    }

    // Notification en fonction du rôle
    public function getNotification($idUsers, $rolesUsers, $id_stores)
    {
        return $this->homeService->getNotification($idUsers, $rolesUsers, $id_stores);
    }

    // Filtrage des produits par prix
    public function FilterproduitsByPrice($data)
    {
        return $this->homeService->FilterproduitsByPrice($data);
    }

    // Tri des produits par catégorie
    public function categoriesproduits($data)
    {
        return $this->homeService->categoriesproduits($data);
    }

    // Tri des produits par sous-catégorie
    public function souscategoriesproduits($data, $data2)
    {
        return $this->homeService->souscategoriesproduits($data, $data2);
    }

    // Tri des produits par sous-sous-catégorie
    public function Soussouscategoriesproduits($data, $data2)
    {
        return $this->homeService->Soussouscategoriesproduits($data, $data2);
    }

    // HomeController

    public function getAllPropriete()
    {
        return $this->homeService->getAllPropriete();
    }

    public function getDeatailSop($id)
    {
        return $this->homeService->getDeatailSop($id);
    }

    public function searchRange($valeur)
    {
        return $this->homeService->searchRange($valeur);
    }

    public function searchRangeByItem($valeur, $item)
    {
        return $this->homeService->searchRangeByItem($valeur, $item);
    }

    public function searchProduct($valeur)
    {
        return $this->homeService->searchProduct($valeur);
    }

    public function selectRealisation($valeur)
    {
        return $this->homeService->selectRealisation($valeur);
    }

    public function SelectOccasion($valeur)
    {
        return $this->homeService->SelectOccasion($valeur);
    }

    public function searchProductByItem($valeur, $item)
    {
        return $this->homeService->searchProductByItem($valeur, $item);
    }

    public function searchstores($valeur)
    {
        return $this->homeService->searchstores($valeur);
    }


    // Vérification de la boutique
    public function verifBoutique($valeur)
    {
        return $this->homeService->verifBoutique($valeur);
    }

    // Vérification de l'email
    public function verifEmail($valeur)
    {
        return $this->homeService->verifEmail($valeur);
    }

    // Enregistrement de la boutique
    public function saveStore(Request $request)
    {
        return $this->homeService->saveStore($request);
    }

    // Enregistrement d'un livreur
    public function savelivreurs(Request $request)
    {
        return $this->homeService->savelivreurs($request);
    }

    // Ajouter un produit à la liste de souhaits
    public function Addsouhait(Request $request)
    {
        return $this->homeService->Addsouhait($request);
    }

    // Récupérer la liste des souhaits par utilisateur
    public function getAllwishlistByUsers($id)
    {
        return $this->homeService->getAllwishlistByUsers($id);
    }

    // Récupérer les sous-catégories par catégories
    public function getsouscategories(Request $request)
    {
        return $this->homeService->getsouscategories($request->data);
    }

    // Récupérer les sous-sous-catégories
    public function getSousSousCategories(Request $request)
    {
        return $this->homeService->getSousSousCategories($request->data);
    }

    // Récupérer toutes les commandes Kanblan (admin ou gestionnaire)
    public function getAllordersKanblan($id)
    {
        return $this->homeService->getAllordersKanblan($id);
    }

    public function getAllorders(Request $request)
    {
        // Récupérer les paramètres de la requête (page, limit, search)
        $filters = [
            'page' => $request->query('page', 1),   // Par défaut, la page est 1
            'limit' => $request->query('limit', 10), // Par défaut, la limite est 10
            'search' => $request->query('search', null), // Si 'search' est fourni, on l'utilise, sinon null
        ];
    
        // Passer les filtres au service pour récupérer les données
        return $this->homeService->getAllorders($filters);
    }

    // Récupérer la liste des livreurs
    public function getListeLivreurs()
    {
        return $this->homeService->getListeLivreurs();
    }

    // Recherche des commandes par transaction ID
    public function searchCommandes($id)
    {
        return $this->homeService->searchCommandes($id);
    }

    // Récupérer les commandes d'un utilisateur
    public function getAllordersByUsers($id)
    {
        return $this->homeService->getAllordersByUsers($id);
    }

    // Détail des commandes par utilisateur
    public function getdetailCommandes($id)
    {
        return $this->homeService->getdetailCommandes($id);
    }

    // Détail des produits de la commande
    public function getOrdersDetail($id, $users)
    {
        return $this->homeService->getOrdersDetail($id, $users);
    }

    // Ajout d'un partenaire
    public function addpartenaire(Request $request)
    {
        return $this->homeService->addpartenaire($request);
    }

    // Changer le statut d'un partenaire
    public function changestatPartenaires($id, $status)
    {
        return $this->homeService->changestatPartenaires($id, $status);
    }

    // Récupérer la liste des partenaires
    public function getPartenaires($id)
    {
        return $this->homeService->getPartenaires($id);
    }

    // Liste des partenaires actifs
    public function listespartenaire($id)
    {
        return $this->homeService->listespartenaire($id);
    }

    // Ajouter un achat
    public function Addachats(Request $request)
    {
        return $this->homeService->Addachats($request);
    }

    // Ajouter une URL de logo
    public function Addlogosurl(Request $request)
    {
        return $this->homeService->Addlogosurl($request);
    }

    // Ajouter un devis
    public function addDevis(Request $request)
    {
        return $this->homeService->addDevis($request);
    }

    // Ajouter une facture
    public function addfactures(Request $request)
    {
        return $this->homeService->addfactures($request);
    }

    public function placeOrder(Request $request)
    {
        $transactionCode = $this->homeService->PlaceOrder($request);
        return $this->homeService->apiResponse(200, "Commande placée avec succès", ['transaction_code' => $transactionCode], 200);
    }

    public function addcomments(Request $request)
    {
        return $this->homeService->addcomments($request);
    }

    public function getRealisationbyLibelle($id)
    {
        return $this->homeService->getRealisationbyLibelle($id);
    }

    public function getRealisationbyId($id)
    {
        return $this->homeService->getRealisationbyId($id);
    }

    public function deleteImagesGalleries($id)
    {
        return $this->homeService->deleteImagesGalleries($id);
    }

    public function deleteRealisations($id)
    {
        return $this->homeService->deleteRealisations($id);
    }

    public function changestatRealisations($id, $status)
    {
        return $this->homeService->changestatRealisations($id, $status);
    }


    // Action pour changer l'état actif d'une réalisation
    public function isActivedChange($id, $status)
    {
        return $this->homeService->isActivedChange($id, $status);
    }

    // Action pour obtenir toutes les réalisations par statut
    public function getAllRealisationsBystatus($request)
    {
        return $this->homeService->getAllRealisationsBystatus($request);
    }

    // Action pour ajouter des données dans les réglages
    public function addInput(Request $request)
    {
        return $this->homeService->addInput($request);
    }

    // Action pour ajouter des fichiers multiples
    public function addMultifiles(Request $request)
    {
        return $this->homeService->AddMultifiles($request);
    }

    // Action pour mettre à jour les informations d'un profil
    public function profilUpdate(Request $request)
    {
        return $this->homeService->profilUpdate($request);
    }

    // Action pour sauvegarder le titre
    public function saveDataTitle(Request $request)
    {
        return $this->homeService->SaveDataTitle($request);
    }

    // Action pour obtenir les positions des réalisations
    public function getPositions()
    {
        return $this->homeService->getPositions();
    }

    // Action pour ajouter une politique
    public function addPolitique(Request $request)
    {
        return $this->homeService->Addpolitique($request);
    }

    // Action pour obtenir toutes les politiques
    public function getAllPolitique()
    {
        return $this->homeService->getAllpolitique();
    }

    // Action pour obtenir tous les réglages
    public function getReglages()
    {
        return $this->homeService->getreglages();
    }

    // Action pour obtenir toutes les publicités
    public function getAllPubs()
    {
        return $this->homeService->getAllpubs();
    }

    // Action pour obtenir toutes les équipes
    public function getEquipe()
    {
        return $this->homeService->getEquipe();
    }

    // Action pour obtenir toutes les réalisations
    public function getAllRealisations(Request $request)
    {
        // Récupérer les paramètres de la requête (page, limit, search)
        $filters = [
            'page' => $request->query('page', 1),   // Par défaut, la page est 1
            'limit' => $request->query('limit', 10), // Par défaut, la limite est 10
            'search' => $request->query('search', null), // Si 'search' est fourni, on l'utilise, sinon null
        ];
        // return $this->homeService->getAllorders($filters);
        return $this->homeService->getAllRealisations($filters);
    }

    // Action pour obtenir tous les abonnés
    public function getAllSubscribers()
    {
        return $this->homeService->getAllSubscribers();
    }

    // Action pour obtenir toutes les images d'une réalisation spécifique
    public function getAllImgRealisations($id)
    {
        return $this->homeService->getAllimgRealisations($id);
    }

    // Action pour supprimer une image d'une réalisation
    public function removeImagesRealisation($id_img_realisations, $realisations_id)
    {
        return $this->homeService->removeImagesRealisation($id_img_realisations, $realisations_id);
    }

    // Action pour supprimer une réalisation
    public function removeRealisation($id)
    {
        return $this->homeService->removeRealisation($id);
    }


        // Action pour récupérer toutes les images de la galerie
        public function getAllGallerieImages(Request $request)
        {
            // Récupérer les paramètres de la requête (page, limit, search)
            $filters = [
                'page' => $request->query('page', 1),   // Par défaut, la page est 1
                'limit' => $request->query('limit', 10), // Par défaut, la limite est 10
                'search' => $request->query('search', null), // Si 'search' est fourni, on l'utilise, sinon null
            ];
            return $this->homeService->getAllgallerieImages($filters);
        }
    
        // Action pour sauvegarder les images de la galerie
        public function saveGallerieImages(Request $request)
        {
            return $this->homeService->SavegallerieImages($request);
        }
    
        // Action pour enregistrer les réalisations
        public function saveRealisations(Request $request)
        {
            return $this->homeService->SaveRealisations($request);
        }
    
        // Action pour mettre à jour les réalisations
        public function updateRealisations(Request $request)
        {
            return $this->homeService->updateRealisations($request);
        }
    
        // Action pour ajouter un message
        public function addMessages(Request $request)
        {
            return $this->homeService->addMessages($request);
        }
    
        // Action pour ajouter une signature
        public function addSignature(Request $request)
        {
            return $this->homeService->AddSignature($request);
        }
    
        // Action pour récupérer toutes les signatures
        public function getAllSignatures()
        {
            return $this->homeService->getAllsignature();
        }
    
        // Action pour ajouter une inscription à la newsletter
        public function addNewsletterSubscriber(Request $request)
        {
            return $this->homeService->subscribers($request);
        }
    
        // Action pour ajouter une visite
        public function addVisit(Request $request)
        {
            return $this->homeService->AddVisites($request);
        }
    
        // Action pour ajouter une nouvelle newsletter
        public function addNewletter(Request $request)
        {
            return $this->homeService->addnewletter($request);
        }
    
        // Action pour récupérer toutes les newsletters
        public function getAllNewsletters()
        {
            return $this->homeService->getAllnewsletters();
        }

    // Récupérer les employés
    public function employees()
    {
        return $this->homeService->employees();
    }

    // Récupérer les commentaires d'un blog par ID
    public function getAllCommenteById($id)
    {
        return $this->homeService->getAllCommenteById($id);
    }

    // Récupérer un magasin par ID
    public function getStoreById($id)
    {
        return $this->homeService->getStoreById($id);
    }

    // Ajouter un commentaire produit
    public function addproduitscomments(Request $request)
    {
        return $this->homeService->addproduitscomments($request);
    }

    // Récupérer tous les commentaires produits par ID
    public function getAllproduitscommentsById($id)
    {
        return $this->homeService->getAllproduitscommentsById($id);
    }







}
