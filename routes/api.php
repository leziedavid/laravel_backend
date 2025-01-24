<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\TransactionController;

use Laravel\Sanctum\Http\Controllers\CsrfCookieController;



Route::group(['prefix' => 'v1/'], function () {
    // Route::get('sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

    Route::get('statistique-orders/{id}', [AuthController::class, 'statistiqueOrders']);
    Route::get('statistique-visites', [AuthController::class, 'statistiqueVisites']);
    Route::get('statistique-visites-datil/{id}', [AuthController::class, 'statistiqueVisitesDatil']);
    Route::get('listes-visites/{id}', [AuthController::class, 'ListesVisites']);
    Route::get('historique-orders-pays', [AuthController::class, 'HistoriqueOrdersPays']);
    Route::get('get-allb-users/{id}', [AuthController::class, 'getAllbUsers']);
    Route::get('get-detail-users/{id}', [AuthController::class, 'getDetailUsersById']);
    Route::get('search-users/{valeur}', [AuthController::class, 'searchUsers']);
    Route::get('get-all-blogs/{id}', [AuthController::class, 'getAllBlogs']);

    // Route pour récupérer tous les articles
    Route::get('articles', [AuthController::class, 'getAllArticles']);
    // Route pour valider un article par ID et son statut
    Route::put('articles/{id}/{status}', [AuthController::class, 'validerArticles']);
    // Route pour supprimer un article
    Route::delete('articles/{id}', [AuthController::class, 'deletePoste']);
    // Route pour récupérer les statistiques du tableau de bord pour une boutique spécifique
    Route::get('dashboard/store/{id}', [AuthController::class, 'getDashboardStore']);
    // Route pour récupérer les statistiques globales du tableau de bord
    Route::get('dashboard/{id}', [AuthController::class, 'getDashboard']);
    // Route pour gérer la connexion de l'utilisateur
    Route::post('auth/login', [AuthController::class, 'login']);


    // Route pour changer le statut d'un utilisateur
    Route::post('users/{id}/status/{status}', [AuthController::class, 'changeStatutUsers']);
    // Route pour récupérer le catalogue d'un blog
    Route::get('catalogue/{id}/blog', [AuthController::class, 'getCatalogueBlog']);
    // Route pour supprimer les images d'un catalogue de blog
    Route::delete('catalogue/{catalogues}/images/{produits}', [AuthController::class, 'removeImagesCatalogueBlog']);
    // Route pour supprimer un utilisateur
    Route::delete('user/{id}', [AuthController::class, 'deleteUser']);
    // Route pour ajouter un utilisateur
    Route::post('user', [AuthController::class, 'addUser']);
    // Route pour mettre à jour les données d'un utilisateur
    Route::put('user/update', [AuthController::class, 'updateUser']);
    // Route pour créer un article
    Route::post('article', [AuthController::class, 'createArticle']);
    // Route pour mettre à jour un article
    Route::put('article/update', [AuthController::class, 'updateArticle']);
    // Route pour créer une catégorie de blog
    Route::post('category', [AuthController::class, 'createCategory']);
    // Route pour mettre à jour une catégorie de blog
    Route::put('category/update', [AuthController::class, 'updateCategory']);

    // Liste des catégories de blog
    Route::get('categories/blog', [AuthController::class, 'getlisteCategoriesBlog']);

    // Liste des catégories de blog actives
    Route::get('categories', [AuthController::class, 'getlisteCategories']);

    // Vérifier les blogs par catégorie
    Route::get('blogs/category/{id}', [AuthController::class, 'checkBycategoriyBlog']);

    // Récupérer un article par ID
    Route::get('article/{id}', [AuthController::class, 'getArticlesById']);

    // Supprimer une catégorie d'article
    Route::delete('category/{id}', [AuthController::class, 'deletecategoriesArticles']);

    // Récupérer une catégorie par ID
    Route::get('category/{id}', [AuthController::class, 'getCategoriesById']);

    // Récupérer les blogs d'une catégorie par ID
    Route::get('category/{id}/blogs', [AuthController::class, 'CategoriesBlogById']);

// import de la compta 

    Route::post('transactions/import', [TransactionController::class, 'import']);
    Route::get('transactions', [TransactionController::class, 'alltransactions']);

    Route::post('confirmation-mail', [HomeController::class, 'confirmationMail']);
    Route::post('send-emails-devis', [HomeController::class, 'sendEmailsDevis']);
    Route::post('changes-stat-order/{id}/{status}', [HomeController::class, 'changesstatOrder']);
    Route::get('notifications/{idUsers}/{rolesUsers}/{id_stores}', [HomeController::class, 'getNotification']);
    Route::post('filter-produits-by-price/{data}', [HomeController::class, 'FilterproduitsByPrice']);
    Route::post('categories-produits/{data}', [HomeController::class, 'categoriesproduits']);
    Route::post('souscategories-produits/{data}/{data2}', [HomeController::class, 'souscategoriesproduits']);
    Route::post('soussouscategories-produits/{data}/{data2}', [HomeController::class, 'Soussouscategoriesproduits']);

    Route::get('propriete', [HomeController::class, 'getAllPropriete']);
    Route::get('details/{id}', [HomeController::class, 'getDeatailSop']);
    Route::get('search-range/{valeur}', [HomeController::class, 'searchRange']);
    Route::get('search-range/{valeur}/{item}', [HomeController::class, 'searchRangeByItem']);
    Route::get('search-product/{valeur}', [HomeController::class, 'searchProduct']);
    Route::get('select-realisation/{valeur}', [HomeController::class, 'selectRealisation']);
    Route::get('select-occasion/{valeur}', [HomeController::class, 'SelectOccasion']);
    Route::get('search-product-by-item/{valeur}/{item}', [HomeController::class, 'searchProductByItem']);
    Route::get('search-stores/{valeur}', [HomeController::class, 'searchstores']);

    // Routes API pour les différents services

    Route::post('verifBoutique/{valeur}', [HomeController::class, 'verifBoutique']);
    Route::post('verifEmail/{valeur}', [HomeController::class, 'verifEmail']);
    Route::post('saveStore', [HomeController::class, 'saveStore']);
    Route::post('savelivreurs', [HomeController::class, 'savelivreurs']);
    Route::post('addSouhait', [HomeController::class, 'Addsouhait']);
    Route::get('getAllwishlistByUsers/{id}', [HomeController::class, 'getAllwishlistByUsers']);
    Route::post('getsouscategories', [HomeController::class, 'getsouscategories']);
    Route::post('getSousSousCategories', [HomeController::class, 'getSousSousCategories']);
    Route::get('getAllordersKanblan/{id}', [HomeController::class, 'getAllordersKanblan']);
    Route::get('getAllorders', [HomeController::class, 'getAllorders']);
    Route::get('getListeLivreurs', [HomeController::class, 'getListeLivreurs']);
    Route::get('searchCommandes/{id}', [HomeController::class, 'searchCommandes']);
    Route::get('getAllordersByUsers/{id}', [HomeController::class, 'getAllordersByUsers']);
    Route::get('getdetailCommandes/{id}', [HomeController::class, 'getdetailCommandes']);
    Route::get('getOrdersDetail/{id}/{users}', [HomeController::class, 'getOrdersDetail']);

    // Vérification de la boutique
    Route::get('verif-boutique/{valeur}', [HomeController::class, 'verifBoutique']);
    // Vérification de l'email
    Route::get('verif-email/{valeur}', [HomeController::class, 'verifEmail']);
    // Enregistrement de la boutique
    Route::post('save-store', [HomeController::class, 'saveStore']);
    // Enregistrement d'un livreur
    Route::post('save-livreurs', [HomeController::class, 'savelivreurs']);
    // Ajouter un produit à la liste de souhaits
    Route::post('add-souhait', [HomeController::class, 'Addsouhait']);
    // Récupérer la liste des souhaits par utilisateur
    Route::get('wishlist/{id}', [HomeController::class, 'getAllwishlistByUsers']);
    // Récupérer les sous-catégories par catégories
    Route::post('sous-categories', [HomeController::class, 'getsouscategories']);
    // Récupérer les sous-sous-catégories
    Route::post('sous-sous-categories', [HomeController::class, 'getSousSousCategories']);
    // Récupérer toutes les commandes Kanblan (admin ou gestionnaire)
    Route::get('orders-kanblan/{id}', [HomeController::class, 'getAllordersKanblan']);
    // Récupérer toutes les commandes
    Route::get('orders/{id}', [HomeController::class, 'getAllorders']);
    // Récupérer la liste des livreurs
    Route::get('livreurs', [HomeController::class, 'getListeLivreurs']);
    // Recherche des commandes par transaction ID
    Route::get('search-commandes/{id}', [HomeController::class, 'searchCommandes']);
    // Récupérer les commandes d'un utilisateur
    Route::get('orders-by-user/{id}', [HomeController::class, 'getAllordersByUsers']);
    // Détail des commandes par utilisateur
    Route::get('order-detail/{id}/{users}', [HomeController::class, 'getdetailCommandes']);
    // Détail des produits de la commande
    Route::get('orders-detail/{id}/{users}', [HomeController::class, 'getOrdersDetail']);


    Route::post('place-order', [HomeController::class, 'placeOrder']);
    Route::post('add-comment', [HomeController::class, 'addcomments']);
    Route::get('realisation/libelle/{id}', [HomeController::class, 'getRealisationbyLibelle']);
    Route::get('realisation/{id}', [HomeController::class, 'getRealisationbyId']);
    Route::delete('gallery-image/{id}', [HomeController::class, 'deleteImagesGalleries']);
    Route::delete('realisation/{id}', [HomeController::class, 'deleteRealisations']);
    Route::put('realisation/{id}/status/{status}', [HomeController::class, 'changestatRealisations']);

    // Route pour récupérer les informations de compte et les commandes
    Route::get('compte/{id}', [HomeController::class, 'getCompteInfo']);
    // Route pour changer l'état actif d'une réalisation
    Route::put('realisations/{id}/actived/{status}', [HomeController::class, 'isActivedChange']);
    // Route pour obtenir toutes les réalisations par statut
    Route::get('realisations/status/{value}', [HomeController::class, 'getAllRealisationsBystatus']);
    // Route pour ajouter des données dans les réglages
    Route::post('input', [HomeController::class, 'addInput']);
    // Route pour ajouter des fichiers multiples
    Route::post('files', [HomeController::class, 'addMultifiles']);
    // Route pour mettre à jour les informations d'un profil
    Route::put('profil/update', [HomeController::class, 'profilUpdate']);
    // Route pour sauvegarder le titre
    Route::post('title', [HomeController::class, 'saveDataTitle']);
    // Route pour obtenir les positions des réalisations
    Route::get('positions', [HomeController::class, 'getPositions']);
    // Route pour ajouter une politique
    Route::post('politique', [HomeController::class, 'addPolitique']);
    // Route pour obtenir toutes les politiques
    Route::get('politique', [HomeController::class, 'getAllPolitique']);
    // Route pour obtenir tous les réglages
    Route::get('reglages', [HomeController::class, 'getReglages']);
    // Route pour obtenir toutes les publicités
    Route::get('pubs', [HomeController::class, 'getAllPubs']);
    // Route pour obtenir toutes les équipes
    Route::get('equipe', [HomeController::class, 'getEquipe']);
    // Route pour obtenir toutes les réalisations
    Route::get('realisations', [HomeController::class, 'getAllRealisations']);
    // Route pour obtenir tous les abonnés
    Route::get('subscribers', [HomeController::class, 'getAllSubscribers']);
    // Route pour obtenir toutes les images d'une réalisation spécifique
    Route::get('realisations/{id}/images', [HomeController::class, 'getAllImgRealisations']);
    // Route pour supprimer une image d'une réalisation
    Route::delete('realisations/images/{id_img_realisations}/remove/{realisations_id}', [HomeController::class, 'removeImagesRealisation']);
    // Route pour supprimer une réalisation
    Route::delete('realisations/{id}', [HomeController::class, 'removeRealisation']);
    // Route pour obtenir toutes les images de la galerie
    Route::get('gallerie-images', [HomeController::class, 'getAllGallerieImages']);
    // Route pour sauvegarder les images de la galerie
    Route::post('savegallerie-images', [HomeController::class, 'saveGallerieImages']);
    // Route pour enregistrer les réalisations
    Route::post('save-realisations', [HomeController::class, 'saveRealisations']);
    // Route pour mettre à jour les réalisations
    Route::post('update-realisations', [HomeController::class, 'updateRealisations']);
    // Route pour ajouter un message
    Route::post('messages', [HomeController::class, 'addMessages']);
    // Route pour ajouter une signature
    Route::post('signature', [HomeController::class, 'addSignature']);
    // Route pour obtenir toutes les signatures
    Route::get('signatures', [HomeController::class, 'getAllSignatures']);
    // Route pour ajouter une inscription à la newsletter
    Route::post('newsletter/subscribe', [HomeController::class, 'addNewsletterSubscriber']);
    // Route pour ajouter une visite
    Route::post('visites', [HomeController::class, 'addVisit']);
    // Route pour ajouter une nouvelle newsletter
    Route::post('newsletter', [HomeController::class, 'addNewletter']);
    // Route pour obtenir toutes les newsletters
    Route::get('newsletters', [HomeController::class, 'getAllNewsletters']);
    // Route pour récupérer les employés
    Route::get('employees', [HomeController::class, 'employees']);
    // Route pour récupérer les commentaires d'un blog par ID
    Route::get('comments/blog/{id}', [HomeController::class, 'getAllCommenteById']);
    // Route pour récupérer un magasin par ID
    Route::get('store/{id}', [HomeController::class, 'getStoreById']);
    // Route pour ajouter un commentaire produit
    Route::post('products/comments', [HomeController::class, 'addproduitscomments']);
    // Route pour récupérer les commentaires d'un produit par ID
    Route::get('products/comments/{id}', [HomeController::class, 'getAllproduitscommentsById']);

    Route::get('homepage-data', [HomeController::class, 'index']);
    Route::get('all-menu', [HomeController::class, 'MegaMenu']);

    // lancer des commandes
    Route::post('addOrders', [HomeController::class,'Addachats']);
    Route::post('saveAllImages', [HomeController::class,'SaveAllImages']);

    // Route pour obtenir tous les produits d'une boutique
    Route::get('produits/store/{id}', [ProduitController::class, 'getAllProduitsByStore']);
    // Route pour rechercher des produits par date d'ajout dans une boutique
    Route::get('produits/search/date/{date1}/{date2}/store/{id}', [ProduitController::class, 'searchProductbyDateByStores']);
    // Route pour rechercher des produits par nom dans une boutique
    Route::get('produits/search/{valeur}/store/{id}', [ProduitController::class, 'searchProductByStoreId']);
    // Route pour obtenir toutes les commandes d'une boutique
    Route::get('orders/store/{id}', [ProduitController::class, 'getAllOrdersByStore']);
    // Route pour obtenir les produits et leurs catégories
    Route::get('produits/index/{id}', [ProduitController::class, 'index']);
    // Route pour obtenir les produits par état (faibles / rupture de stock)
    Route::get('produits/state/{id}', [ProduitController::class, 'getAllProduitsByState']);
    // Route pour obtenir toutes les catégories de produits
    Route::get('categories/store/{id}', [ProduitController::class, 'getAllCategoriesBySearch']);

    // Récupérer toutes les catégories
    Route::get('categories/{id}', [ProduitController::class, 'getAllCategories']);
    // Récupérer toutes les catégories produits
    Route::get('categories-produits/{id}', [ProduitController::class, 'getAllCategoriesByProduits']);
    // Récupérer les sous-catégories sélectionnées
    Route::get('sous-categories/{id}', [ProduitController::class, 'selectedSouscategories']);
    // Récupérer toutes les sous-catégories
    Route::get('sous-categories-index/{id}', [ProduitController::class, 'getAllSouscategoriesIndex']);
    // Editer une sous-catégorie
    Route::get('edit-sous-categorie/{id}/{idcategories}', [ProduitController::class, 'editSouscategories']);
    // Récupérer les options de réalisation par état
    Route::get('options-realisation', [ProduitController::class, 'getOptionRealisationByState']);
    // Récupérer une option de réalisation par son ID
    Route::get('option-realisation/{id}', [ProduitController::class, 'getOpRealisation']);
    // Récupérer toutes les options de réalisation
    Route::get('all-options-realisation/{id}', [ProduitController::class, 'getAllOptionRealisation']);
    // Supprimer une option de réalisation
    Route::delete('delete-option-realisation/{id}', [ProduitController::class, 'deleteOptionRealisation']);
    // Changer le statut d'une option de réalisation
    Route::put('change-status-option-realisation/{id}/{status}', [ProduitController::class, 'changeStateOptionRealisation']);
    // Editer une option de réalisation
    Route::get('edit-option-realisation/{id}', [ProduitController::class, 'editOptionRealisation']);
    // Sauvegarder une option de réalisation
    Route::post('save-option-realisation', [ProduitController::class, 'saveOptionRealisation']);
    // Mettre à jour une option de réalisation
    Route::put('update-option-realisation', [ProduitController::class, 'updateOptionRealisation']);

    // Couleurs
    Route::get('couleurs/{id}/all', [ProduitController::class, 'getAllCouleurs']);
    Route::delete('couleurs/{id}', [ProduitController::class, 'deleteCouleurs']);
    Route::put('couleurs/{id}/state/{status}', [ProduitController::class, 'changesstatCouleurs']);
    Route::get('couleurs/{id}/edit', [ProduitController::class, 'editCouleurs']);
    Route::post('couleurs/save', [ProduitController::class, 'SaveCouleurs']);
    Route::put('couleurs/update', [ProduitController::class, 'updateCouleurs']);

    // Tailles
    Route::get('tailles/{id}/all', [ProduitController::class, 'getAlltailles']);
    Route::delete('tailles/{id}', [ProduitController::class, 'deleteTailles']);
    Route::put('tailles/{id}/state/{status}', [ProduitController::class, 'changesstatTailles']);
    Route::get('tailles/{id}/edit', [ProduitController::class, 'editTailles']);
    Route::post('tailles/save', [ProduitController::class, 'SaveTailles']);
    Route::put('tailles/update', [ProduitController::class, 'updateTailles']);

    // Pointures
    Route::get('pointures/{id}/all', [ProduitController::class, 'getAllpointures']);
    Route::delete('pointures/{id}', [ProduitController::class, 'deletePointures']);
    Route::put('pointures/{id}/state/{status}', [ProduitController::class, 'changesstatPointures']);
    Route::get('pointures/{id}/edit', [ProduitController::class, 'editPointures']);
    Route::post('pointures/save', [ProduitController::class, 'SavePointures']);
    Route::put('pointures/update', [ProduitController::class, 'updatePointures']);


    // Route pour obtenir toutes les sous-catégories
    Route::get('subcategories/{id}', [ProduitController::class, 'getAllsouscategories']);
    // Route pour sauvegarder les sous-catégories
    Route::post('subcategories', [ProduitController::class, 'saveSouscategories']);
    // Route pour supprimer une sous-catégorie
    Route::delete('subcategories/{id}', [ProduitController::class, 'deletesouscategories']);
    // Route pour mettre à jour une sous-catégorie
    Route::put('subcategories', [ProduitController::class, 'updatesouscategories']);
    // Route pour ajouter une catégorie
    Route::post('categories', [ProduitController::class, 'addcategories']);
    // Route pour mettre à jour une catégorie
    Route::put('categories', [ProduitController::class, 'updatecategories']);
    // Route pour supprimer une sous-catégorie
    Route::delete('souscategories/{id}', [ProduitController::class, 'deletecategoriesSouscategorie']);
    // Route pour supprimer une sous-sous-catégorie
    Route::delete('soussouscategories/{id}', [ProduitController::class, 'deletecategoriesSoussouscategorie']);
    // Route pour supprimer un produit
    Route::delete('products/{id}', [ProduitController::class, 'deletecategoriesproduits']);
    // Route pour supprimer une catégorie
    Route::delete('categories/{id}', [ProduitController::class, 'deletecategories']);
    // Route pour obtenir une catégorie par son ID
    Route::get('categories/{id}', [ProduitController::class, 'editCatégories']);
    // Route pour obtenir tous les mails
    Route::get('mails/{id}', [ProduitController::class, 'getAllmails']);
    // Route pour obtenir toutes les boutiques
    Route::get('stores/{id}', [ProduitController::class, 'getAllboutiques']);
    // Route pour obtenir toutes les boutiques sans pagination
    Route::get('stores/all/{id}', [ProduitController::class, 'getAllboutiquesByAdd']);
    // Route pour changer le statut des boutiques
    Route::put('stores/status/{id}/{status}', [ProduitController::class, 'changesstatStores']);
    // Route pour changer le statut d'un produit
    Route::put('products/status/{id}/{status}', [ProduitController::class, 'status_products']);
    // Route pour changer le statut d'une catégorie
    Route::put('categories/status/{id}/{status}', [ProduitController::class, 'changescategoriesStates']);
    // Route pour changer le statut d'une sous-catégorie
    Route::put('subcategories/status/{id}/{status}', [ProduitController::class, 'changesSouscategoriesStates']);
    // Route pour changer le statut d'une sous-sous-catégorie
    Route::put('soussouscategories/status/{id}/{status}', [ProduitController::class, 'changesSousSouscategoriesState']);
    // Route pour supprimer un produit
    Route::delete('products/{id}', [ProduitController::class, 'deleteproducts']);
    // Route pour supprimer une boutique
    Route::delete('stores/{id}', [ProduitController::class, 'deleteStores']);

    // Routes pour gérer les sous-sous catégories
    Route::get('soussouscategories', [ProduitController::class, 'getAllsoussouscategories']);
    Route::get('soussouscategories/edit/{id}/{idsousCat}', [ProduitController::class, 'editsoussousCategories']);
    Route::get('soussouscategories/selected/{id}', [ProduitController::class, 'selectedsoussouscategories']);
    Route::post('soussouscategories/save', [ProduitController::class, 'saveSousSouscategories']);
    Route::post('soussouscategories/update', [ProduitController::class, 'updatesoussouscategories']);
    Route::delete('soussouscategories/delete/{id}', [ProduitController::class, 'deletesoussouscategories']);

    // Routes pour gérer les produits    
    Route::post('produit/add', [ProduitController::class, 'addProduits']);
    Route::post('produit/update', [ProduitController::class, 'updateproduits']);
    // Route pour supprimer une image
    Route::delete('remove-images/{cataloges}/{produits}', [ProduitController::class, 'removeImages']);
    // Route pour vérifier les paramètres d'un produit
    Route::get('check-prams/{prams}/{id}', [ProduitController::class, 'checkPrams']);
    // Route pour ajouter un paramètre
    Route::post('add-parametre', [ProduitController::class, 'addparametre']);
    // Route pour uploader des fichiers
    Route::post('file-upload', [ProduitController::class, 'fileupload']);
    // Route pour récupérer le catalogue d'un produit
    Route::get('get-catalogue/{id}', [ProduitController::class, 'getcatalogue']);
    // Route pour rechercher des produits par date
    Route::get('search-products-by-date/{date1}/{date2}', [ProduitController::class, 'searchProductbyDate']);
    // Route pour rechercher des commandes par date
    Route::get('search-orders-by-date/{date1}/{date2}', [ProduitController::class, 'searchOdersByDate']);
    // Route pour rechercher les commandes entre deux dates
    Route::get('search-by-orders-date/{date1}/{date2}', [ProduitController::class, 'searchByOrdersDate']);
    // Route pour obtenir toutes les dates de commande
    Route::get('all-orders-date', [ProduitController::class, 'getAllOrdersDate']);
    // Route pour rechercher des produits par magasin
    Route::get('search-products-by-stores/{data}', [ProduitController::class, 'searchProductByStores']);
    // Route pour obtenir les paramètres du produit
    Route::get('get-produit-setting/{data}', [ProduitController::class, 'getProduitsetting']);
});



// Route::middleware(['auth:sanctum'])->group(function () {

//     Route::get("/me", fn(Request $request) => $request->user());

//     Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
//         ->middleware(['throttle:6,1'])
//         ->name('verification.send');

//     Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
//         ->name('logout');

//     Route::get("/projects", [ProjectController::class, 'index']);
//     Route::post("/project", [ProjectController::class, 'store']);
//     Route::get("/project/{project}", [ProjectController::class, 'show']);
//     Route::put("/project/{project}", [ProjectController::class, 'update']);
//     Route::delete("/project/{project}", [ProjectController::class, 'destroy']);


//     // Route::get("project/{project}/boards", [BoardController::class, 'index']);

//     Route::post("/board", [BoardController::class, 'store']);
//     // Route::get("/board/{board}", [BoardController::class, 'show']);
//     Route::put("/board/{board}", [BoardController::class, 'update']);
//     Route::delete("/board/{board}", [BoardController::class, 'destroy']);


//     Route::post("/ticket", [TicketController::class, 'store']);
//     Route::get("/ticket/{ticket}", [TicketController::class, 'show']);
//     Route::put("/ticket/{ticket}", [TicketController::class, 'update']);
//     Route::delete("/ticket/{ticket}", [TicketController::class, 'destroy']);


//     Route::post("/ticket/{ticket}/move", [TicketController::class, 'move']);
//     Route::post("/ticket/{ticket}/assign", [TicketController::class, 'assign']);
// });
