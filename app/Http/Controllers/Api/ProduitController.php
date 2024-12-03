<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProduitService;  // Ajout du service ProduitService

class ProduitController extends Controller
{
    // Injection du service ProduitService
    protected $produitService;

    public function __construct(ProduitService $produitService)
    {
        // Attribution du service injecté à la propriété
        $this->produitService = $produitService;
    }

    // Route pour obtenir tous les produits d'une boutique
    public function getAllProduitsByStore($id)
    {
        return $this->produitService->getAllproduisByStores($id);
    }

    // Route pour rechercher des produits par date d'ajout dans une boutique
    public function searchProductbyDateByStores($date1, $date2, $id)
    {
        return $this->produitService->searchProductbyDateByStores($date1, $date2, $id);
    }

    // Route pour rechercher des produits par nom dans une boutique
    public function searchProductByStoreId($valeur, $id)
    {
        return $this->produitService->searchProductByStoresId($valeur, $id);
    }

    // Route pour obtenir toutes les commandes d'une boutique
    public function getAllOrdersByStore($id)
    {
        return $this->produitService->getAllordersStores($id);
    }

    // Route pour obtenir les produits et leurs catégories
    public function index($id)
    {
        return $this->produitService->index($id);
    }

    // Route pour obtenir les produits par état (faibles / rupture de stock)
    public function getAllProduitsByState($id)
    {
        return $this->produitService->getAllproduisByState($id);
    }

    // Route pour obtenir toutes les catégories de produits
    public function getAllCategoriesBySearch($id)
    {
        return $this->produitService->getAllcategoriesBySearche($id);
    }


    // Route pour récupérer toutes les catégories
    public function getAllCategories($id)
    {
        return $this->produitService->getAllcategories($id);
    }

    // Route pour récupérer toutes les catégories produits
    public function getAllCategoriesByProduits($id)
    {
        return $this->produitService->getAllcategoriesByProduits($id);
    }

    // Route pour récupérer les sous-catégories sélectionnées
    public function selectedSouscategories($id)
    {
        return $this->produitService->selectedSouscategories($id);
    }

    // Route pour récupérer toutes les sous-catégories
    public function getAllSouscategoriesIndex($id)
    {
        return $this->produitService->getAllsouscategorieIndex($id);
    }

    // Route pour éditer une sous-catégorie
    public function editSouscategories($id, $idcategories)
    {
        return $this->produitService->editsousCatégories($id, $idcategories);
    }

    // Route pour récupérer les options de réalisation par état
    public function getOptionRealisationByState()
    {
        return $this->produitService->getOptionRealisationByState();
    }

    // Route pour récupérer une option de réalisation spécifique
    public function getOpRealisation($id)
    {
        return $this->produitService->getOpRealisation($id);
    }

    // Route pour récupérer toutes les options de réalisation
    public function getAllOptionRealisation($id)
    {
        return $this->produitService->getAllOptionRealisation($id);
    }

    // Route pour supprimer une option de réalisation
    public function deleteOptionRealisation($id)
    {
        return $this->produitService->deleteOptionRealisation($id);
    }

    // Route pour changer le statut d'une option de réalisation
    public function changeStateOptionRealisation($id, $status)
    {
        return $this->produitService->changeStateOptionRealisation($id, $status);
    }

    // Route pour éditer une option de réalisation
    public function editOptionRealisation($id)
    {
        return $this->produitService->editOptionRealisation($id);
    }

    // Route pour sauvegarder une option de réalisation
    public function saveOptionRealisation(Request $request)
    {
        return $this->produitService->SaveOptionRealisation($request);
    }

    // Route pour mettre à jour une option de réalisation
    public function updateOptionRealisation(Request $request)
    {
        return $this->produitService->updateOptionRealisation($request);
    }



    // Couleurs
    public function getAllCouleurs($id)
    {
        return $this->produitService->getAllCouleurs($id);
    }

    public function deleteCouleurs($id)
    {
        return $this->produitService->deleteCouleurs($id);
    }

    public function changesstatCouleurs($id, $status)
    {
        return $this->produitService->changesstatCouleurs($id, $status);
    }

    public function editCouleurs($id)
    {
        return $this->produitService->editCouleurs($id);
    }

    public function SaveCouleurs(Request $request)
    {
        return $this->produitService->SaveCouleurs($request);
    }

    public function updateCouleurs(Request $request)
    {
        return $this->produitService->updateCouleurs($request);
    }

    // Tailles
    public function getAlltailles($id)
    {
        return $this->produitService->getAlltailles($id);
    }

    public function deleteTailles($id)
    {
        return $this->produitService->deleteTailles($id);
    }

    public function changesstatTailles($id, $status)
    {
        return $this->produitService->changesstatTailles($id, $status);
    }

    public function editTailles($id)
    {
        return $this->produitService->editTailles($id);
    }

    public function SaveTailles(Request $request)
    {
        return $this->produitService->SaveTailles($request);
    }

    public function updateTailles(Request $request)
    {
        return $this->produitService->updateTailles($request);
    }

    // Pointures
    public function getAllpointures($id)
    {
        return $this->produitService->getAllpointures($id);
    }

    public function deletePointures($id)
    {
        return $this->produitService->deletePointures($id);
    }

    public function changesstatPointures($id, $status)
    {
        return $this->produitService->changesstatPointures($id, $status);
    }

    public function editPointures($id)
    {
        return $this->produitService->editPointures($id);
    }

    public function SavePointures(Request $request)
    {
        return $this->produitService->SavePointures($request);
    }

    public function updatePointures(Request $request)
    {
        return $this->produitService->updatePointures($request);
    }


    // Fonction pour obtenir toutes les sous-catégories
    public function getAllsouscategories($id)
    {
        return $this->produitService->getAllsouscategories($id);
    }

    // Fonction pour sauvegarder les sous-catégories
    public function saveSouscategories(Request $request)
    {
        return $this->produitService->saveSouscategories($request);
    }

    // Fonction pour supprimer une sous-catégorie
    public function deletesouscategories($id)
    {
        return $this->produitService->deletesouscategories($id);
    }

    // Fonction pour mettre à jour une sous-catégorie
    public function updatesouscategories(Request $request)
    {
        return $this->produitService->updatesouscategories($request);
    }

    // Fonction pour ajouter une catégorie
    public function addcategories(Request $request)
    {
        return $this->produitService->addcategories($request);
    }

    // Fonction pour mettre à jour une catégorie
    public function updatecategories(Request $request)
    {
        return $this->produitService->updatecategories($request);
    }

    // Fonction pour supprimer une sous-catégorie
    public function deletecategoriesSouscategorie($id)
    {
        return $this->produitService->deletecategoriesSouscategorie($id);
    }

    // Fonction pour supprimer une sous-sous-catégorie
    public function deletecategoriesSoussouscategorie($id)
    {
        return $this->produitService->deletecategoriesSoussouscategorie($id);
    }

    // Fonction pour supprimer un produit
    public function deletecategoriesproduits($id)
    {
        return $this->produitService->deletecategoriesproduits($id);
    }

    // Fonction pour supprimer une catégorie
    public function deletecategories($id)
    {
        return $this->produitService->deletecategories($id);
    }

    // Fonction pour obtenir une catégorie par son ID
    public function editCatégories($id)
    {
        return $this->produitService->editCatégories($id);
    }

    // Fonction pour obtenir tous les mails
    public function getAllmails($id)
    {
        return $this->produitService->getAllmails($id);
    }

    // Fonction pour obtenir toutes les boutiques
    public function getAllboutiques($id)
    {
        return $this->produitService->getAllboutiques($id);
    }

    // Fonction pour obtenir toutes les boutiques sans pagination
    public function getAllboutiquesByAdd($id)
    {
        return $this->produitService->getAllboutiquesByAdd($id);
    }

    // Fonction pour changer le statut des boutiques
    public function changesstatStores($id, $staus)
    {
        return $this->produitService->changesstatStores($id, $staus);
    }

    // Fonction pour changer le statut d'un produit
    public function status_products($id, $staus)
    {
        return $this->produitService->status_products($id, $staus);
    }

    // Fonction pour changer le statut d'une catégorie
    public function changescategoriesStates($id, $staus)
    {
        return $this->produitService->changescategoriesStates($id, $staus);
    }

    // Fonction pour changer le statut d'une sous-catégorie
    public function changesSouscategoriesStates($id, $staus)
    {
        return $this->produitService->changesSouscategoriesStates($id, $staus);
    }

    // Fonction pour changer le statut d'une sous-sous-catégorie
    public function changesSousSouscategoriesState($id, $staus)
    {
        return $this->produitService->changesSousSouscategoriesState($id, $staus);
    }

    // Fonction pour supprimer un produit
    public function deleteproducts($id)
    {
        return $this->produitService->deleteproducts($id);
    }

    // Fonction pour supprimer une boutique
    public function deleteStores($id)
    {
        return $this->produitService->deleteStores($id);
    }


    // Appel de la méthode getAllsoussouscategories
    public function getAllsoussouscategories(Request $request)
    {
        return $this->produitService->getAllsoussouscategories($request);
    }

    // Appel de la méthode editsoussousCategories
    public function editsoussousCategories($id, $idsousCat)
    {
        return $this->produitService->editsoussousCategories($id, $idsousCat);
    }

    // Appel de la méthode selectedsoussouscategories
    public function selectedsoussouscategories($id)
    {
        return $this->produitService->selectedsoussouscategories($id);
    }

    // Appel de la méthode saveSousSouscategories
    public function saveSousSouscategories(Request $request)
    {
        return $this->produitService->saveSousSouscategories($request);
    }

    // Appel de la méthode updatesoussouscategories
    public function updatesoussouscategories(Request $request)
    {
        return $this->produitService->updatesoussouscategories($request);
    }

    // Appel de la méthode deletesoussouscategories
    public function deletesoussouscategories($id)
    {
        return $this->produitService->deletesoussouscategories($id);
    }

    // Appel de la méthode addProduits
    public function addProduits(Request $request)
    {
        return $this->produitService->addProduits($request);
    }

    // Appel de la méthode updateproduits
    public function updateproduits(Request $request)
    {
        return $this->produitService->updateproduits($request);
    }


    // Route pour supprimer une image
    public function removeImages($cataloges, $produits)
    {
        return $this->produitService->removeImages($cataloges, $produits);
    }

    // Route pour vérifier les paramètres d'un produit
    public function checkPrams($prams, $id)
    {
        return $this->produitService->checkPrams($prams, $id);
    }

    // Route pour ajouter un paramètre
    public function addparametre(Request $request)
    {
        return $this->produitService->addparametre($request);
    }

    // Route pour uploader des fichiers
    public function fileupload(Request $request)
    {
        return $this->produitService->fileupload($request);
    }

    // Route pour récupérer le catalogue d'un produit
    public function getcatalogue($id)
    {
        return $this->produitService->getcatalogue($id);
    }

    // Route pour rechercher des produits par date
    public function searchProductbyDate($date1, $date2)
    {
        return $this->produitService->searchProductbyDate($date1, $date2);
    }

    // Route pour rechercher des commandes par date
    public function searchOdersByDate($date1, $date2)
    {
        return $this->produitService->searchOdersByDate($date1, $date2);
    }

    // Route pour rechercher les commandes entre deux dates
    public function searchByOrdersDate($date1,$date2) {
        return $this->produitService->searchByOrdersDate($date1, $date2);
    }


    // Route pour obtenir toutes les dates de commande
    public function getAllOrdersDate()
    {
        return $this->produitService->getAllOrdersDate();
    }

    // Route pour rechercher des produits par magasin
    public function searchProductByStores($data)
    {
        return $this->produitService->searchProductByStores($data);
    }

    // Route pour obtenir les paramètres du produit
    public function getProduitsetting($data)
    {
        return $this->produitService->getProduitsetting($data);
    }



}
