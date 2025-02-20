<?php

namespace App\Services;  // La déclaration du namespace doit être la première ligne

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\Notifications;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\FuncCall;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    use ApiResponse;


    // Statistiques des commandes
    public function statistiqueOrders($id)
    {
        // Date actuelle
        $aujourdhui = Carbon::now();

        // Nombre de commandes pour le jour en cours
        $ordersJour = DB::table('orders')
            ->whereDate('date_orders', $aujourdhui->toDateString())
            ->count();

        // Nombre de commandes pour la semaine en cours
        $ordersSemaine = DB::table('orders')
            ->whereYear('date_orders', $aujourdhui->year)
            ->where('date_orders', '>=', $aujourdhui->startOfWeek()->toDateString())
            ->where('date_orders', '<=', $aujourdhui->endOfWeek()->toDateString())
            ->count();

        // Nombre de commandes pour le mois en cours
        $ordersMois = DB::table('orders')
            ->whereYear('date_orders', $aujourdhui->year)
            ->whereMonth('date_orders', $aujourdhui->month)
            ->count();

        // Nombre de commandes pour l'année en cours
        $ordersAnnee = DB::table('orders')
            ->whereYear('date_orders', $aujourdhui->year)
            ->count();

        $response = [
            'orders_jour' => $ordersJour,
            'orders_semaine' => $ordersSemaine,
            'orders_mois' => $ordersMois,
            'orders_annee' => $ordersAnnee,
        ];

        return $this->apiResponse(200, "Statistiques des commandes récupérées avec succès", $response, 200);
    }

    // Statistiques des visites
    public function statistiqueVisites()
    {
        // Date actuelle
        $aujourdhui = Carbon::now();

        // Nombre de visites pour le jour en cours
        $visitesJour = DB::table('visites')
            ->whereDate(DB::raw("STR_TO_DATE(date_visites, '%d-%m-%Y')"), $aujourdhui->toDateString())
            ->count();

        // Nombre de visites pour la semaine en cours
        $visitesSemaine = DB::table('visites')
            ->whereYear(DB::raw("STR_TO_DATE(date_visites, '%d-%m-%Y')"), $aujourdhui->year)
            ->where('date_visites', '>=', $aujourdhui->startOfWeek()->format('d-m-Y'))
            ->where('date_visites', '<=', $aujourdhui->endOfWeek()->format('d-m-Y'))
            ->count();

        // Nombre de visites pour le mois en cours
        $visitesMois = DB::table('visites')
            ->whereYear(DB::raw("STR_TO_DATE(date_visites, '%d-%m-%Y')"), $aujourdhui->year)
            ->whereMonth(DB::raw("STR_TO_DATE(date_visites, '%d-%m-%Y')"), $aujourdhui->month)
            ->count();

        // Nombre de visites pour l'année en cours
        $visitesAnnee = DB::table('visites')
            ->whereYear(DB::raw("STR_TO_DATE(date_visites, '%d-%m-%Y')"), $aujourdhui->year)
            ->count();

        $response = [
            'visites_jour' => $visitesJour,
            'visites_semaine' => $visitesSemaine,
            'visites_mois' => $visitesMois,
            'visites_annee' => $visitesAnnee,
        ];

        return $this->apiResponse(200, "Statistiques des visites récupérées avec succès", $response, 200);
    }

    // Statistiques détaillées des visites
    public function statistiqueVisitesDatil($id)
    {
        // Nombre de visites par jour
        $visitesParJour = DB::table('visites')
            ->select(DB::raw("DATE_FORMAT(STR_TO_DATE(date_visites, '%d-%m-%Y'), '%Y-%m-%d') as date"), DB::raw('COUNT(*) as nombre_visites'))
            ->groupBy('date')
            ->get();

        // Nombre de visites par semaine
        $visitesParSemaine = DB::table('visites')
            ->select(DB::raw('YEAR(STR_TO_DATE(date_visites, "%d-%m-%Y")) as annee'), DB::raw('WEEK(STR_TO_DATE(date_visites, "%d-%m-%Y")) as semaine'), DB::raw('COUNT(*) as nombre_visites'))
            ->groupBy('annee', 'semaine')
            ->get();

        // Nombre de visites par mois
        $visitesParMois = DB::table('visites')
            ->select(DB::raw('YEAR(STR_TO_DATE(date_visites, "%d-%m-%Y")) as annee'), DB::raw('MONTH(STR_TO_DATE(date_visites, "%d-%m-%Y")) as mois'), DB::raw('COUNT(*) as nombre_visites'))
            ->groupBy('annee', 'mois')
            ->get();

        $response = [
            'visites_par_jour' => $visitesParJour,
            'visites_par_semaine' => $visitesParSemaine,
            'visites_par_mois' => $visitesParMois,
        ];

        return $this->apiResponse(200, "Statistiques détaillées des visites récupérées avec succès", $response, 200);
    }

    // Liste des visites
    public function ListesVisites($id)
    {
        $users = DB::table('visites')
            ->orderBy('id_visites', 'desc')
            ->paginate(8);

        return $this->apiResponse(200, "Liste des visites récupérées avec succès", $users, 200);
    }

    // Historique des commandes payées
    public function HistoriqueOrdersPays()
    {
        $orders = DB::table('orders')
            ->select(DB::raw('SUM(total) as total_pays'))
            ->get();

        return $this->apiResponse(200, "Historique des commandes payées récupéré avec succès", $orders, 200);
    }

    // Liste des utilisateurs sauf celui qui a l'ID donné
    public function getAllbUsers($id)
    {
        $users = DB::table('users')
            ->where('users.id', '!=', $id)
            ->orderBy('users.id', 'desc')
            ->latest()->paginate(3);
        return $this->apiResponse(200, "Liste des utilisateurs récupérés avec succès", $users, 200);
    }

    // Détail d'un utilisateur par ID
    public function getDetailUsersById($id)
    {
        $datas = DB::table('users')
            ->where('users.id', '=', $id)
            ->orderBy('users.id', 'desc')
            ->get();

        return $this->apiResponse(200, "Détail de l'utilisateur récupéré avec succès", $datas, 200);
    }

    // Recherche des utilisateurs par nom
    public function searchUsers($valeur)
    {
        $users = DB::table('users')
            ->where('users.name', 'like', '%' . $valeur . '%')
            ->orderBy('users.id', 'desc')
            ->distinct()
            ->get();

        return $this->apiResponse(200, "Recherche d'utilisateurs effectuée avec succès", $users, 200);
    }

    // Récupérer tous les blogs
    public function getAllBlogs($id)
    {
        $Allblogs = DB::table('posts')
            ->join('users', 'users.id', '=', 'posts.posts_user_id')
            ->join('categories_blog', 'categories_blog.categories_blog_id', '=', 'posts.posts_category_id')
            ->paginate(6);

        $categories = DB::table('categories_blog')
            ->orderBy('categories_blog.categories_blog_id', 'desc')
            ->get();

        $response = [
            'datas' => $Allblogs,
            'categories' => $categories,
        ];

        return $this->apiResponse(200, "Liste des blogs récupérée avec succès", $response, 200);
    }


    public function getAllArticles()
    {
        // Récupération des articles
        $Allblogs = DB::table('posts')
            ->join('users', 'users.id', '=', 'posts.posts_user_id')
            ->join('categories_blog', 'categories_blog.categories_blog_id', '=', 'posts.posts_category_id')
            ->where('posts.posts_status', '=', 1)
            ->where('categories_blog.state_categories_blog', '=', 1)
            ->get();

        // Récupération des catégories
        $categories = DB::table('categories_blog')
            ->where('categories_blog.state_categories_blog', '=', 1)
            ->orderBy('categories_blog.categories_blog_id', 'desc')
            ->get();

        // Réponse
        $response = [
            'data' => $Allblogs,
            'categories' => $categories,
        ];
        // Retourne une réponse structurée
        return $this->apiResponse(200, "Articles et catégories récupérés avec succès", $response, 200);
    }

    public function ValiderArticles($id, $status)
    {
        // Mise à jour du statut de l'article
        DB::table('posts')
            ->where('posts.posts_id', '=', $id)
            ->update(['posts_status' => $status]);
        // Message de confirmation
        $messages = "L'article a été publié avec succès";
        // Retourne une réponse structurée
        return $this->apiResponse(200, $messages, [], 200);
    }

    public function deletePoste($id)
    {
        // Suppression de l'article
        DB::table('posts')
            ->where('posts.posts_id', '=', $id)
            ->delete();

        // Message de confirmation
        $messages = "L'article a été supprimé avec succès";
        // Retourne une réponse structurée
        return $this->apiResponse(200, $messages, [], 200);
    }

    public function getdashboardStore($id)
    {
        // Récupération des données de la boutique et des produits associés
        $usersDatas = DB::table('users')
            ->join('stores', 'stores.idUsers_stores', '=', 'users.id')
            ->where('users.id', '=', $id)
            ->get();

        $idBoutiques = 0;
        $theDatas = json_decode($usersDatas);
        $idBoutiques = $theDatas[0]->id_stores;

        // Données des produits
        $dataProduits = DB::table('products')
            ->where('products.id_boutique', '=', $idBoutiques)
            ->where('products.status_products', '=', 1)
            ->get();

        // Stock faible
        $Stockfaible = DB::table('products')
            ->where('products.id_boutique', '=', $idBoutiques)
            ->where('products.status_products', '=', 1)
            ->where('products.stoks', '=', 2)
            ->get();

        // Rupture de stock
        $Rupturestock = DB::table('products')
            ->where('products.id_boutique', '=', $idBoutiques)
            ->where('products.status_products', '=', 1)
            ->where('products.stoks', '=', 0)
            ->get();

        // Commandes récentes
        $ordersListes = DB::table('orders')
            ->join('order_product', 'order_product.order_id', '=', 'orders.id_orders')
            ->join('stores', 'stores.id_stores', '=', 'order_product.stores_id')
            ->where('stores.id_stores', '=', $idBoutiques)
            ->where('orders.status_orders', '=', 0)
            ->orderBy('orders.id_orders', 'desc')
            ->distinct()
            ->limit(5)
            ->get();

        // Historique des commandes
        $OrdersHistoriques = DB::table('orders')
            ->join('order_product', 'order_product.order_id', '=', 'orders.id_orders')
            ->join('stores', 'stores.id_stores', '=', 'order_product.stores_id')
            ->where('stores.id_stores', '=', $idBoutiques)
            ->where('orders.status_orders', '=', 5)
            ->orderBy('orders.id_orders', 'desc')
            ->limit(5)
            ->distinct()
            ->get();

        // Total des paiements
        $orders = DB::table('orders')
            ->join('order_product', 'order_product.order_id', '=', 'orders.id_orders')
            ->join('stores', 'stores.id_stores', '=', 'order_product.stores_id')
            ->where('stores.id_stores', '=', $idBoutiques)
            ->select(DB::raw('SUM(orders.total) as total_pays'))
            ->get();

        $response = [
            'dataProduits' => $dataProduits,
            'OrdersHistoriques' => $OrdersHistoriques,
            'orders' => $orders,
            'ordersListes' => $ordersListes,
            'Rupturestock' => $Rupturestock,
            'Stockfaible' => $Stockfaible,
        ];

        // Retourne une réponse structurée
        return $this->apiResponse(200, "Données du tableau de bord récupérées avec succès", $response, 200);
    }

    public function getdashboard($id)
    {
        // Récupération des données générales du tableau de bord
        $dataBoutique = DB::table('stores')
            ->where('status_stores', '=', 1)
            ->get();

        $dataProduits = DB::table('products')
            ->where('status_products', '=', 1)
            ->get();

        $Stockfaible = DB::table('products')
            ->join('stores', 'stores.id_stores', '=', 'products.id_boutique')
            ->where('products.status_products', '=', 1)
            ->where('products.stoks', '=', 2)
            ->get();

        $Rupturestock = DB::table('products')
            ->join('stores', 'stores.id_stores', '=', 'products.id_boutique')
            ->where('status_products', '=', 1)
            ->where('products.stoks', '=', 0)
            ->get();

        $dataUsers = DB::table('users')
            ->where('status', '=', 1)
            ->get();

        $dataOrders = DB::table('orders')->get();

        // Commandes récentes
        $ordersListes = DB::table('orders')
            ->where('orders.status_orders', '=', 0)
            ->orderBy('orders.id_orders', 'desc')
            ->distinct()
            ->limit(5)
            ->get();

        // Historique des commandes
        $OrdersHistoriques = DB::table('orders')
            ->where('orders.status_orders', '=', 5)
            ->orderBy('orders.id_orders', 'desc')
            ->limit(5)
            ->distinct()
            ->get();

        // Meilleures boutiques
        $meilleuresboutiques = DB::table('order_product')
            ->join('stores', 'stores.id_stores', '=', 'order_product.stores_id')
            ->orderBy('stores.id_stores', 'desc')
            ->distinct()
            ->get();

        $dataCategories = DB::table('categories')->get();
        $dataSub_categories = DB::table('sub_categories')->get();
        $dataSub_sub_categories = DB::table('sub_sub_categories')->get();

        // Total des paiements
        $orders = DB::table('orders')
            ->select(DB::raw('SUM(total) as total_pays'))
            ->get();

        $response = [
            'dataBoutique' => $dataBoutique,
            'dataProduits' => $dataProduits,
            'dataOrders' => $dataOrders,
            'OrdersHistoriques' => $OrdersHistoriques,
            'dataCategories' => $dataCategories,
            'dataSub_categories' => $dataSub_categories,
            'dataSub_sub_categories' => $dataSub_sub_categories,
            'dataUsers' => $dataUsers,
            'orders' => $orders,
            'ordersListes' => $ordersListes,
            'meilleuresboutiques' => $meilleuresboutiques,
            'Rupturestock' => $Rupturestock,
            'Stockfaible' => $Stockfaible,
        ];

        // Retourne une réponse structurée
        return $this->apiResponse(200, "Tableau de bord récupéré avec succès", $response, 200);
    }

    /**
     * Connexion utilisateur et génération d'un token Sanctum
     */
    public function login(Request $request)
    {
        // Validation des données d'entrée
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Récupérer l'utilisateur actif avec Eloquent
        $user = User::where('email', $request->email)
            ->where('status', 1)
            ->first();

        if (!$user) {
            return $this->apiResponse(401, 'Identifiant incorrect', null, 401);
        }

        // Vérifier le mot de passe
        if (!Hash::check($request->password, $user->password)) {
            return $this->apiResponse(401, 'Mot de passe incorrect', null, 401);
        }
        // Créer un token avec l'ID de l'utilisateur
        // $token = $user->createToken('YourAppName', ['id:' . $user->id])->plainTextToken;

            // Définir la date d'expiration du token (ex: 60 minutes)
            $expiration = now()->addMinutes(60)->format('Y-m-d H:i:s');
            // Créer un token avec une expiration intégrée dans les capacités du token
            $token = $user->createToken('YourAppName', ['id:' . $user->id, 'expires_at:' . $expiration])->plainTextToken;

        // Enregistrer la session de l'utilisateur
        DB::table('sessions')->insert([
            'user_id' => $user->id,
            'ip_address' => $request->ip(), // Récupérer l'adresse IP
            'user_agent' => $request->header('User-Agent'), // Récupérer le User-Agent
            'payload' => $token, // Enregistrer le token dans payload
            'last_activity' => now()->timestamp, // Enregistrer l'heure de dernière activité
        ]);

        return $this->apiResponse(200, 'Connexion réussie', [
            'user' => $user->is_admin,
            'token' => $token
        ], 200);


    }


    /**
     * Récupérer les données de l'utilisateur en utilisant le token
     *
     * @param string $token
     * @return mixed
     */
    public function getUserDataFromToken($token)
    {
        // Décodez le token pour extraire l'ID de l'utilisateur
        $userId = $this->getUserIdFromToken($token);

        if ($userId) {
            // Récupérer les données de l'utilisateur avec l'ID
            $user = User::find($userId);
            if ($user) {
                return $this->apiResponse(200, 'success', $user, 200);
            }else{
            return $this->apiResponse(404, 'Utilisateur non trouvé', '', 404);
            }
        }
        return $this->apiResponse(401, 'Token invalide', '', 401);
    }

    public function getUserIdFromToken($token)
    {
        try {
            // Décoder le token en utilisant Sanctum
            $user = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
            // Si le token est valide, retourner l'ID de l'utilisateur
            if ($user) {
                return $user->tokenable->id;  // Retourne l'ID de l'utilisateur
            }
        } catch (\Exception $e) {

            dd($e);
            // Si une erreur se produit pendant le décodage, retourne null
            return null;
        }
    
        // return null;  // Si le token est invalide
    }


    /**
     * Gère la déconnexion de l'utilisateur.
     *
     * @param string $token
     * @return mixed
     */

    public function logout(string $token)
    {
        // Debug : Vérifier que le token est bien reçu
        // dd($token);
    
        // Vérifier si le token existe dans la table personal_access_tokens
        $tokenData = PersonalAccessToken::findToken($token);
    
        if (!$tokenData) {
            return $this->apiResponse(400, 'Token invalide ou utilisateur non trouvé', null, 400);
        }
    
        // Récupérer l'utilisateur associé au token
        $user = $tokenData->tokenable;
    
        if ($user) {
            // Supprimer le token spécifique
            $tokenData->delete();
    
            return $this->apiResponse(200, 'Déconnexion réussie', null, 200);
        }
    
        return $this->apiResponse(400, 'Échec de la déconnexion', null, 400);
    }
    

    
    
    

    public function getUserIdFromToken2($token)
{
    // Supprime le "Bearer " du token s'il est passé dans les headers
    if (substr($token, 0, 7) === "Bearer ") {
        $token = substr($token, 7);
    }

    try {
        // Décoder le token en utilisant Sanctum
        $tokenInstance = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

        // Si le token est valide, retourner l'ID de l'utilisateur
        if ($tokenInstance) {
            return $tokenInstance->tokenable->id;
        }
    } catch (\Exception $e) {
        // Si une erreur se produit pendant le décodage, retourne null
        return null;
    }

    return null;  // Si le token est invalide
}



    public function changesstatUsers($id, $status)
    {
        $donnes = DB::table('users')
            ->where('id', '=', $id)
            ->update(['status' => $status]);
        return $this->apiResponse(200, "Statut de l\'utilisateur mis à jour avec succès", [], 200);
    }

    public function getcatalogueBlog($id)
    {
        $donnes = DB::table('gallery')
            ->join('gallery_blog', 'gallery_blog.id_gallery', '=', 'gallery.gallery_id')
            ->where('gallery_blog.id_posts', $id)->get();

        return $this->apiResponse(200, "Réponse de connexion", $donnes, 200);
    }

    public function removeImagesCatalogueBlog($cataloges, $produits)
    {
        DB::table('gallery')
            ->where('gallery.gallery_id', $cataloges)
            ->delete();

        DB::table('gallery_blog')
            ->where('gallery_blog.id_gallery', $cataloges)
            ->delete();


        return $this->apiResponse(200, "Images supprimées avec succès", [], 200);
    }

    public function deleteUsers($id)
    {
        DB::table('users')
            ->where('id', '=', $id)->delete();
        return $this->apiResponse(200, "Compte supprimé avec succès", [], 200);
    }

/**
     * Ajout d'un nouvel utilisateur et génération d'un token Sanctum
     */
    public function Addusers(Request $request)
    {

        // Création de l'utilisateur
        $user = User::create([
            'name' => $request->nom . " " . $request->prenom,
            'contact' => $request->numero,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 1,
            'created_at' => now(),
            'is_admin' => 'Clients', // Par défaut, utilisateur standard
        ]);

        if (!$user) {
            return $this->apiResponse(500, "Quelque chose s'est mal passé, veuillez recommencer", null, 500);
        }

        // Créer un token avec l'ID de l'utilisateur
        $token = $user->createToken('YourAppName', ['id:' . $user->id])->plainTextToken;

        return $this->apiResponse(201, 'Votre compte a été créé avec succès', [
            // 'user' => $user,
            'token' => $token,
        ], 201);
    }


    public function updateDataUsers($request)
    {
        $passwords = '';
        if ($request->password) {
            $passwords = Hash::make($request->password);
        }

        DB::table('users')
            ->where('id', '=', $request->id)
            ->update([
                'name' => $request->nom,
                'contact' => $request->contact,
                'email' => $request->email,
                'nom_ntreprise' => $request->entreprise,
                'lieu_livraison' => $request->lieu_livraison,
                'pays' => $request->pays,
                'ville' => $request->ville,
                'quartier' => $request->quartier,
            ]);

        return $this->apiResponse(200, "Réponse de connexion", [], 200);
    }

    public function createarticles($request)
    {
        $uploadedFiles = $request->images;
        $slug = Str::slug($request->posts_title, '-') . '-' . 'Tarafe';
        $today = date("Y-m-d");
        $relativePath = "";
        $status = 2;
        $extension = "";

        $dir = 'Blogs/';
        $str1 = '0123456789';
        $shuffled1 = str_shuffle($str1);
        $code = "BlogTarafe-" . substr($shuffled1, 0, 5);
        $absolutePath = public_path($dir);
        $extension = $uploadedFiles->getClientOriginalExtension();
        $filename = $code . '.' . $extension;
        $uploadedFiles->move($absolutePath, $filename);
        $relativePath = $dir . $filename;

        $idBlogs = DB::table('posts')->insertGetId([
            'posts_title' => $request->title,
            'posts_slug' => $slug,
            'posts_status' => $status,
            'posts_user_id' => $request->posts_user_id,
            'posts_category_id' => $request->posts_category_id,
            'posts_body' => $request->posts_description,
            'imagePath' => $relativePath,
            'created_at' => $today,
        ]);

        // Creation des images de blog
        foreach ($request->file('files') as $fichiers) {
            $relativePaths = "";
            $dir = 'ImagesBlogs/';
            $shuffled = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $shuffled1 = str_shuffle('0123456789');
            $code = "Tarafe-" . substr($shuffled1, 0, 5) . substr($shuffled, 0, 1);
            $absolutePath = public_path($dir);
            $extension = $fichiers->getClientOriginalExtension();
            $filename = $code . '.' . $extension;
            $fichiers->move($absolutePath, $filename);
            $relativePaths = $dir . $filename;

            $idgallery = DB::table('gallery')->insertGetId([
                'Path_gallery' => $relativePaths,
                'created_at' => $today,
            ]);

            DB::table('gallery_blog')->insert([
                'id_gallery' => $idgallery,
                'id_posts' => $idBlogs,
                'created_at' => $today,
            ]);
        }

        return $this->apiResponse(200, "Article ajouté avec succès", [], 200);
    }

    public function updateArticles($request)
    {
        // Logique similaire à la méthode createarticles mais avec une mise à jour.
        $uploadedFiles = $request->images;
        $today = date("Y-m-d");

        if ($request->images) {
            $slug = Str::slug($request->libelle, '-') . '-' . 'Tarafe';
            $relativePath = "";
            $status = 2;
            $extension = "";

            $dir = 'Blogs/';
            $shuffled1 = str_shuffle('0123456789');
            $code = "BlogTarafe-" . substr($shuffled1, 0, 5);
            $absolutePath = public_path($dir);
            $extension = $uploadedFiles->getClientOriginalExtension();
            $filename = $code . '.' . $extension;
            $uploadedFiles->move($absolutePath, $filename);
            $relativePath = $dir . $filename;

            DB::table('posts')
                ->where('posts_id', '=', $request->posts_id)
                ->update([
                    'posts_title' => $request->libelle,
                    'posts_slug' => $slug,
                    'posts_user_id' => $request->posts_user_id,
                    'posts_category_id' => $request->selected1,
                    'posts_body' => $request->description,
                    'imagePath' => $relativePath,
                ]);
        }

        return $this->apiResponse(200, "Article mis à jour avec succès", [], 200);
    }

    public function categoriesCreate($request)
    {
        $slug = Str::slug($request->libelle, '-') . '-' . 'Tarafe';
        $today = date("Y-m-d");

        $response = DB::table('categories_blog')->insertGetId([
            'name_categories_blog' => $request->libelle,
            'slug_categories_blog' => $slug,
            'created_at' => $today,
        ]);


        return $this->apiResponse(200, "Réponse de connexion", [], 200);
    }

    public function updatecategoriesblog($request)
    {
        $response =  DB::table('categories_blog')
            ->where('categories_blog_id', '=', $request->id)
            ->update([
                'name_categories_blog' => $request->libelle,
                'slug_categories_blog' => $request->slug,
            ]);

        return $this->apiResponse(200, "Catégorie mise à jour avec succès", [], 200);
    }

    // Méthode pour récupérer la liste des catégories de blog
    public function getlisteCategoriesBlog()
    {
        $response = DB::table('categories_blog')
            ->orderBy('categories_blog.categories_blog_id', 'desc')
            ->paginate(3);

        return $this->apiResponse(200, "Liste des catégories de blog récupérée avec succès", $response, 200);
    }

    // Méthode pour récupérer les catégories de blog actives
    public function getlisteCategories()
    {
        $listeCategories = DB::table('categories_blog')
            ->where('categories_blog.state_categories_blog', '=', 1)
            ->get();

        return $this->apiResponse(200, "Liste des catégories actives récupérée avec succès", $listeCategories, 200);
    }

    // Méthode pour vérifier les blogs par catégorie
    public function checkBycategoriyBlog($id)
    {
        $blogs = DB::table('posts')
            ->join('users', 'users.id', '=', 'posts.posts_user_id')
            ->join('categories_blog', 'categories_blog.categories_blog_id', '=', 'posts.posts_category_id')
            ->where('posts.posts_status', '=', 1)
            ->where('posts.posts_category_id', '=', $id)
            ->get();

        return $this->apiResponse(200, "Blogs récupérés pour la catégorie spécifiée", $blogs, 200);
    }

    // Méthode pour récupérer un article par ID
    public function getArticlesById($id)
    {
        $blogs = DB::table('posts')
            ->join('users', 'users.id', '=', 'posts.posts_user_id')
            ->join('categories_blog', 'categories_blog.categories_blog_id', '=', 'posts.posts_category_id')
            ->where('posts.posts_id', '=', $id)
            ->get();

        $Allblogs = DB::table('posts')
            ->join('users', 'users.id', '=', 'posts.posts_user_id')
            ->join('categories_blog', 'categories_blog.categories_blog_id', '=', 'posts.posts_category_id')
            ->where('posts.posts_id', '!=', $id)
            ->get();

        $Allcategories = DB::table('categories_blog')
            ->orderBy('categories_blog.categories_blog_id', 'desc')
            ->get();

        $gallery = DB::table('gallery_blog')
            ->join('gallery', 'gallery.gallery_id', '=', 'gallery_blog.id_gallery')
            ->join('posts', 'posts.posts_id', '=', 'gallery_blog.id_posts')
            ->where('posts.posts_id', '=', $id)
            ->orderBy('gallery.gallery_id', 'desc')
            ->get();

        $response = [
            'data' => $blogs,
            'Allblogs' => $Allblogs,
            'Allcategories' => $Allcategories,
            'gallery' => $gallery,
        ];

        return $this->apiResponse(200, "Article et informations associées récupérés avec succès", $response, 200);
    }

    // Méthode pour supprimer une catégorie d'article
    public function deletecategoriesArticles($id)
    {
        DB::table('categories_blog')
            ->where('categories_blog_id', '=', $id)
            ->delete();

        $messages = "Catégorie supprimée avec succès";

        return $this->apiResponse(200, $messages, [], 200);
    }

    // Méthode pour récupérer une catégorie par ID
    public function getCategoriesById($id)
    {
        $data = DB::table('categories_blog')
            ->where('categories_blog_id', '=', $id)
            ->where('categories_blog.state_categories_blog', '=', 1)
            ->get();

        return $this->apiResponse(200, "Catégorie récupérée avec succès", $data, 200);
    }

    // Méthode pour récupérer les blogs d'une catégorie par ID
    public function CategoriesBlogById($id)
    {
        $blogs = DB::table('posts')
            ->join('users', 'users.id', '=', 'posts.posts_user_id')
            ->join('categories_blog', 'categories_blog.categories_blog_id', '=', 'posts.posts_category_id')
            ->where('posts.posts_status', '=', 1)
            ->where('categories_blog.state_categories_blog', '=', 1)
            ->where('posts.posts_category_id', '=', $id)
            ->get();

        return $this->apiResponse(200, "Blogs récupérés pour la catégorie spécifiée", $blogs, 200);
    }

    // nouvealle fonction :



    
}
