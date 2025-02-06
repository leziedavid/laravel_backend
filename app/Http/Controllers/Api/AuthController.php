<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;  // Importation du service AuthService

use App\Traits\ApiResponse;
class AuthController extends Controller
{

    use ApiResponse;

    // Déclaration de la propriété pour stocker l'instance du service
    protected $authService;

    // Injection du service AuthService via le constructeur
    public function __construct(AuthService $authService)
    {
        // Initialisation de la propriété
        $this->authService = $authService;
    }

    // Exemple de méthode pour récupérer les statistiques des commandes
    public function statistiqueOrders($id)
    {
        // Appel de la méthode statistiqueOrders du AuthService
        return $this->authService->statistiqueOrders($id);
    }

    // Exemple de méthode pour récupérer les statistiques des visites
    public function statistiqueVisites()
    {
        // Appel de la méthode statistiqueVisites du AuthService
        return $this->authService->statistiqueVisites();
    }

    // Exemple de méthode pour récupérer les statistiques détaillées des visites
    public function statistiqueVisitesDatil($id)
    {
        // Appel de la méthode statistiqueVisitesDatil du AuthService
        return $this->authService->statistiqueVisitesDatil($id);
    }

    // Exemple de méthode pour récupérer la liste des visites
    public function ListesVisites($id)
    {
        // Appel de la méthode ListesVisites du AuthService
        return $this->authService->ListesVisites($id);
    }

    // Exemple de méthode pour récupérer l'historique des commandes payées
    public function HistoriqueOrdersPays()
    {
        // Appel de la méthode HistoriqueOrdersPays du AuthService
        return $this->authService->HistoriqueOrdersPays();
    }

    // Exemple de méthode pour récupérer tous les utilisateurs sauf celui donné
    public function getAllbUsers($id)
    {
        // Appel de la méthode getAllbUsers du AuthService
        return $this->authService->getAllbUsers($id);
    }

    // Exemple de méthode pour récupérer le détail d'un utilisateur par ID
    public function getDetailUsersById($id)
    {
        // Appel de la méthode getDetailUsersById du AuthService
        return $this->authService->getDetailUsersById($id);
    }

    // Exemple de méthode pour rechercher des utilisateurs
    public function searchUsers($valeur)
    {
        // Appel de la méthode searchUsers du AuthService
        return $this->authService->searchUsers($valeur);
    }

    // Exemple de méthode pour récupérer tous les blogs
    public function getAllBlogs($id)
    {
        // Appel de la méthode getAllBlogs du AuthService
        return $this->authService->getAllBlogs($id);
    }

    // Méthode pour récupérer tous les articles
    public function getAllArticles()
    {
        // Appel de la méthode getAllArticles du AuthService
        return $this->authService->getAllArticles();
    }

    // Méthode pour valider un article par ID et son statut
    public function validerArticles($id, $status)
    {
        // Appel de la méthode ValiderArticles du AuthService
        return $this->authService->ValiderArticles($id, $status);
    }

    // Méthode pour supprimer un article
    public function deletePoste($id)
    {
        // Appel de la méthode deletePoste du AuthService
        return $this->authService->deletePoste($id);
    }

    // Méthode pour récupérer les statistiques du tableau de bord pour une boutique spécifique
    public function getDashboardStore($id)
    {
        // Appel de la méthode getdashboardStore du AuthService
        return $this->authService->getdashboardStore($id);
    }

    // Méthode pour récupérer les statistiques globales du tableau de bord
    public function getDashboard($id)
    {
        // Appel de la méthode getdashboard du AuthService
        return $this->authService->getdashboard($id);
    }

    // Méthode pour gérer la connexion de l'utilisateur
    public function login(Request $request)
    {
        // Appel de la méthode login du AuthService
        return $this->authService->login($request);
    }

    public function getUserFromToken(Request $request)
    {
        // Récupérer le token de la requête
        $token = $request->query('token');
        if (!$token) {
            return $this->apiResponse(400, "Token invalide ou manquant.", [], 400);
        }
        // Appel de la méthode du service
        return $this->authService->getUserDataFromToken($token);
    }

    public function logout(Request $request)
    {
        // Récupérer le token depuis l'en-tête Authorization
        $token = $request->bearerToken();
    
        // Si le token n'est pas dans l'en-tête, le récupérer depuis le corps de la requête
        if (!$token) {
            $token = $request->input('token');
        }
    
        // Vérifier que le token est bien une chaîne non vide
        if (!$token || !is_string($token)) {
            return $this->apiResponse(400, "Token invalide ou manquant.", [], 400);
        }
    
        return $this->authService->logout($token);
    }
    
    
    // Méthode pour changer le statut d'un utilisateur
    public function changeStatutUsers($id, $status)
    {
        return $this->authService->changesstatUsers($id, $status);
    }

    // Méthode pour récupérer le catalogue des blogs
    public function getCatalogueBlog($id)
    {
        return $this->authService->getcatalogueBlog($id);
    }

    // Méthode pour supprimer les images d'un catalogue de blog
    public function removeImagesCatalogueBlog($catalogues, $produits)
    {
        return $this->authService->removeImagesCatalogueBlog($catalogues, $produits);
    }

    // Méthode pour supprimer un utilisateur
    public function deleteUser($id)
    {
        return $this->authService->deleteUsers($id);
    }

    // Méthode pour ajouter un utilisateur
    public function addUser(Request $request)
    {
        return $this->authService->Addusers($request);
    }

    // Méthode pour mettre à jour les informations d'un utilisateur
    public function updateUser(Request $request)
    {
        return $this->authService->updateDataUsers($request);
    }

    // Méthode pour créer un article
    public function createArticle(Request $request)
    {
        return $this->authService->createarticles($request);
    }

    // Méthode pour mettre à jour un article
    public function updateArticle(Request $request)
    {
        return $this->authService->updateArticles($request);
    }

    // Méthode pour créer une catégorie de blog
    public function createCategory(Request $request)
    {
        return $this->authService->categoriesCreate($request);
    }

    // Méthode pour mettre à jour une catégorie de blog
    public function updateCategory(Request $request)
    {
        return $this->authService->updatecategoriesblog($request);
    }



    // Méthode pour récupérer la liste des catégories de blog
    public function getlisteCategoriesBlog()
    {
        return $this->authService->getlisteCategoriesBlog();
    }

    // Méthode pour récupérer les catégories de blog actives
    public function getlisteCategories()
    {
        return $this->authService->getlisteCategories();
    }

    // Méthode pour vérifier les blogs par catégorie
    public function checkBycategoriyBlog($id)
    {
        return $this->authService->checkBycategoriyBlog($id);
    }

    // Méthode pour récupérer un article par ID
    public function getArticlesById($id)
    {
        return $this->authService->getArticlesById($id);
    }

    // Méthode pour supprimer une catégorie d'article
    public function deletecategoriesArticles($id)
    {
        return $this->authService->deletecategoriesArticles($id);
    }

    // Méthode pour récupérer une catégorie par ID
    public function getCategoriesById($id)
    {
        return $this->authService->getCategoriesById($id);
    }

    // Méthode pour récupérer les blogs d'une catégorie par ID
    public function CategoriesBlogById($id)
    {
        return $this->authService->CategoriesBlogById($id);
    }


    
}
