<?php

namespace App\Services;  // La déclaration du namespace doit être la première ligne

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notifications;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HomeService
{
    use ApiResponse;

    // Confirmation de commande par mail
    public function confirmationMail($request)
    {
        $ordersData = DB::table('orders')
            ->where('id_orders', '=', $request->id_commende)
            ->get();

        $datas = json_decode($ordersData);
        $nomePrenom = $datas[0]->nomUsers_orders;
        $transactionId = $datas[0]->transaction_id;
        $emails = $datas[0]->email_orders;
        $MontantTotal = $datas[0]->total;
        $ModeP = $datas[0]->Mode_paiement;

        $hello = "Bonjour, " . $nomePrenom;
        $message = "Votre commande a été enregistrée avec succès....";
        $Montant = "Montant total de la commande : " . $MontantTotal . " FCFA";
        $Modepaiement = "Mode de paiement utilisé: " . $ModeP;
        $condition = "Si vous rencontrez des difficultés, contactez-nous.";

        $state = 2;
        $sujet = "Confirmation de commande N° " . $transactionId;
        $emailAdmin = "contact@tarafe.com";
        $email = $emails;
        $Users = $nomePrenom;
        $id = 2;

        $maildata = [
            'state' => $state,
            'message' => $message,
            'condition' => $condition,
            'hello' => $hello,
            'sujet' => $sujet,
            'Users' => $Users,
            'Montant' => $Montant,
            'Modepaiement' => $Modepaiement,
            'emailAdmin' => $emailAdmin,
            'url' => 'http://127.0.0.1:8000/comptevalidate/' . $id
        ];

        Mail::to($email)->send(new Notifications($maildata));

        // Utilisation du trait pour la réponse
        return $this->apiResponse(200, "Le statut de la commande a été modifié avec succès", null, 200);
    }

    // Envoi d'email pour devis
    public function sendEmailsDevis($request)
    {
        $ordersData = DB::table('orders')
            ->where('id_orders', '=', $request->idOrders)
            ->get();

        $datas = json_decode($ordersData);
        $transactionId = $datas[0]->transaction_id;
        $nomePrenom = $datas[0]->nomUsers_orders;
        $emails = $datas[0]->email_orders;

        $hello = "Bonjour, " . $nomePrenom;
        $message = "Nous espérons que ce message vous trouve bien. Nous sommes ravis de vous informer que votre commande a été personnalisée avec soin.";
        $Devis = "Ci-joint, veuillez trouver le devis détaillé pour votre commande, comprenant tous les coûts mentionnés ci-dessus.";
        $pub = "Nous sommes impatients de vous fournir des produits de qualité et de vous offrir une expérience d'achat exceptionnelle.";
        $condition = "Si vous rencontrez des difficultés, contactez-nous à l'adresse : contact@tarafe.com";

        $state = 4;
        $sujet = "Confirmation de commande personnalisée N° " . $transactionId;

        $maildata = [
            'state' => $state,
            'message' => $message,
            'condition' => $condition,
            'hello' => $hello,
            'sujet' => $sujet,
            'Users' => $nomePrenom,
            'Devis' => $Devis,
            'pub' => $pub,
            'emailAdmin' => "contact@tarafe.com",
            'url' => $request->fichiers,
        ];

        Mail::to($emails)->send(new Notifications($maildata));

        // Utilisation du trait pour la réponse
        return $this->apiResponse(200, "Le statut de la commande a été modifié avec succès", null, 200);
    }

    // Modification du statut de la commande
    public function changesstatOrder($id, $status)
    {
        DB::table('orders')->where('id_orders', '=', $id)
            ->update(['status_orders' => $status]);

        $ordersData = DB::table('orders')->where('id_orders', '=', $id)->get();
        $datas = json_decode($ordersData);
        $nomePrenom = $datas[0]->nomUsers_orders;
        $transactionId = $datas[0]->transaction_id;
        $emails = $datas[0]->email_orders;
        $MontantTotal = $datas[0]->total;
        $ModeP = $datas[0]->Mode_paiement;

        $hello = "Bonjour, " . $nomePrenom;
        $message = $this->getOrderStatusMessage($status);
        $Montant = "Montant total de la commande : " . $MontantTotal . " FCFA";
        $Modepaiement = "Mode de paiement utilisé: " . $ModeP;
        $condition = "Si vous rencontrez des difficultés, contactez-nous.";

        $state = 2;
        $sujet = "Confirmation de commande N° " . $transactionId;
        $emailAdmin = "contact@tarafe.com";
        $email = $emails;
        $Users = $nomePrenom;
        $id = 2;

        $maildata = [
            'state' => $state,
            'message' => $message,
            'condition' => $condition,
            'hello' => $hello,
            'sujet' => $sujet,
            'Users' => $Users,
            'Montant' => $Montant,
            'Modepaiement' => $Modepaiement,
            'emailAdmin' => $emailAdmin,
            'url' => 'http://127.0.0.1:8000/comptevalidate/' . $id
        ];

        Mail::to($email)->send(new Notifications($maildata));

        // Utilisation du trait pour la réponse
        return $this->apiResponse(200, "Le statut de la commande a été modifié avec succès", null, 200);
    }

    // Notification en fonction du rôle
    public function getNotification($idUsers, $rolesUsers, $id_stores)
    {
        $data = '';
        if ($rolesUsers == "Clients") {
            $data = DB::table('orders')
                ->join('users', 'users.id', '=', 'orders.user_id')
                ->where('users.id', '=', $idUsers)
                ->where('orders.status_orders', '=', 0)
                ->orderBy('orders.id_orders', 'desc')
                ->get();
        } elseif ($rolesUsers == "Vendeurs") {
            $data = DB::table('orders')
                ->join('order_product', 'order_product.order_id', '=', 'orders.id_orders')
                ->join('stores', 'stores.id_stores', '=', 'order_product.stores_id')
                ->where('stores.id_stores', '=', $id_stores)
                ->where('orders.status_orders', '=', 0)
                ->orderBy('orders.id_orders', 'desc')
                ->get();
        } elseif ($rolesUsers == "Admin") {
            $data = DB::table('orders')
                ->where('orders.status_orders', '=', 0)
                ->orderBy('orders.id_orders', 'desc')
                ->get();
        }

        // Utilisation du trait pour la réponse
        return $this->apiResponse(200, "Notifications récupérées", $data, 200);
    }

    // Filtrage des produits par prix
    public function FilterproduitsByPrice($data)
    {
        $products = DB::table('products')
            ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
            ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
            ->where('products.price', '=', $data)
            ->where('products.status_products', 1)
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();

        // Utilisation du trait pour la réponse
        return $this->apiResponse(200, "Produits filtrés par prix", $products, 200);
    }

    // Tri des produits par catégorie
    public function categoriesproduits($data)
    {
        $tabdata = explode(",", $data);
        $products = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->whereIn('categoriesByproduits.categoriesId', $tabdata)
            ->where('products.status_products', 1)
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();

        // Utilisation du trait pour la réponse
        return $this->apiResponse(200, "Produits triés par catégorie", $products, 200);
    }

    // Tri des produits par sous-catégorie
    public function souscategoriesproduits($data, $data2)
    {
        $tabdata = explode(",", $data);
        $tabdata2 = explode(",", $data2);
        $products = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->whereIn('categoriesByproduits.categoriesId', $tabdata)
            ->whereIn('categoriesByproduits.sous_categoriesId', $tabdata2)
            ->where('products.status_products', 1)
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();

        // Utilisation du trait pour la réponse
        return $this->apiResponse(200, "Produits triés par sous-catégorie", $products, 200);
    }

    // Tri des produits par sous-sous-catégorie
    public function Soussouscategoriesproduits($data, $data2)
    {
        $tabdata = explode(",", $data);
        $tabdata2 = explode(",", $data2);
        $products = DB::table('products')
            ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
            ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
            ->whereIn('category_product.category_id', $tabdata)
            ->whereIn('category_product.sub_categories_id', $tabdata2)
            ->where('products.status_products', 1)
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();

        // Utilisation du trait pour la réponse
        return $this->apiResponse(200, "Produits triés par sous-sous-catégorie", $products, 200);
    }

    // Ajoute cette méthode dans ta classe HomeService
    private function getOrderStatusMessage($status)
    {
        switch ($status) {
            case 0:
                return "Votre commande est en cours de traitement.";
            case 1:
                return "Votre commande a été expédiée.";
            case 2:
                return "Votre commande a été livrée.";
            case 3:
                return "Votre commande a été annulée.";
            default:
                return "Statut inconnu.";
        }
    }

    // Fonction pour récupérer les informations des comptes et commandes des utilisateurs
    public function getCompteInfo($id)
    {
        $users = DB::table('users')
            ->where('users.id', '!=', $id)
            ->orderBy('users.id', 'desc')
            ->get();

        $Commande = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->join('order_product', 'order_product.order_id', '=', 'orders.id_orders')
            ->join('products', 'products.id_products', '=', 'order_product.product_id')
            ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
            ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
            ->orderBy('orders.id_orders', 'desc')
            ->get();

        $response = [
            'Commande' => $Commande,
            'data' => $users,
        ];

        // Utilisation du trait pour la réponse
        return $this->apiResponse(200, "Detail du compte", $response, 200);
    }

    // Fonction pour récupérer les produits par sous-catégories
    public function getProduitsBySousCategories($parametre)
    {
        $categories = DB::table('categories')
            ->orderBy('categories.id_categories', 'desc')
            ->get();

        $item = 1;
        $products = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->join('sous_categories_produits', 'sous_categories_produits.idcategories_produits', '=', 'categories_produits.id_categories_produits')
            ->where('sous_categories_produits.libelle_sous_categories_produits', '=', $parametre)
            ->where('products.status_products', $item)
            ->orderBy('products.id_products', 'DESC')
            ->distinct()
            ->paginate(6);

        $response = [
            'categories' => $categories,
            'products' => $products,
            'parametre' => $parametre,
        ];

        return $this->apiResponse(200, "Produits triés par sous-catégorie", $response, 200);

        return response()->json($response);
    }

    // Fonction pour récupérer les produits par catégorie
    public function getProduitsbycategories($value)
    {
        $categories = DB::table('categories')
            ->orderBy('categories.id_categories', 'desc')
            ->get();

        $item = 1;
        $products = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->where('categories_produits.libelle_categories_produits', '=', $value)
            ->where('products.status_products', $item)
            ->orderBy('products.id_products', 'DESC')
            ->distinct()
            ->paginate(6);

        $response = [
            'categories' => $categories,
            'products' => $products,
        ];

        return response()->json($response);
    }

    // Fonction pour récupérer les produits nouveautés
    public function getNouveauteProduits()
    {
        $categories = DB::table('categories_produits')
            ->orderBy('categories_produits.id_categories_produits', 'desc')
            ->get();

        $item = 1;
        $products = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->where('products.status_products', $item)
            ->orderBy('products.id_products', 'DESC')
            ->distinct()
            ->paginate(6);

        $response = [
            'categories' => $categories,
            'products' => $products,
        ];

        return response()->json($response);
    }

    // Fonction pour générer un Mega Menu
    public function MegaMenu()
    {
        $item = 1;
        $categories = DB::table('categories_produits')
            ->orderBy('categories_produits.id_categories_produits', 'desc')
            ->where('categories_produits.state_categories_produits', $item)
            ->get();

        $outp = "";
        foreach ($categories as $value) {
            if ($outp != "") {
                $outp .= ",";
            }

            $outp .= '{"id_categories_produits":"' . $value->id_categories_produits . '",';
            $outp .= '"libelle_categories_produits":"' . $value->libelle_categories_produits . '",';
            $outp .= '"slug_categories_produits":"' . $value->slug_categories_produits . '",';
            $outp .= '"logos_categories_produits":"' . $value->logos_categories_produits . '",';
            $outp .= '"logos_size":"' . $value->logos_size . '",';
            $outp .= '"created_at":"' . $value->created_at . '",';

            $sousCategories_produits = DB::table('sous_categories_produits')
                ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'sous_categories_produits.idcategories_produits')
                ->where('sous_categories_produits.idcategories_produits', '=', $value->id_categories_produits)
                ->where('sous_categories_produits.state_sous_categories_produits', $item)
                ->orderBy('sous_categories_produits.id_sous_categories_produits', 'desc')
                ->get();

            $outp1 = "";
            foreach ($sousCategories_produits as $values) {
                if ($outp1 != "") {
                    $outp1 .= ",";
                }
                $outp1 .= '{"id_sous_categories_produits":"' . $values->id_sous_categories_produits . '",';
                $outp1 .= '"idcategories_produits":"' . $values->idcategories_produits . '",';
                $outp1 .= '"libelle_sous_categories_produits":"' . $values->libelle_sous_categories_produits . '",';
                $outp1 .= '"slug_sous_categories_produits":"' . $values->slug_sous_categories_produits . '",';
                $outp1 .= '"created_at":"' . $values->created_at . '"}';
            }

            $outp1 = "[" . $outp1 . "]";
            $outp .= '"listescategories":' . $outp1 . '}';
        }

        $response = "[" . $outp . "]";
        return $this->apiResponse(200, "Produits triés par  catégorie", $response, 200);
    }

    // Fonction pour l'index de la page d'accueil
    public function index()
    {
        $sliders = DB::table('sliders')
            ->orderBy('sliders.id_sliders', 'desc')
            ->get();

        $categories = DB::table('categories_produits')
            ->orderBy('categories_produits.id_categories_produits', 'desc')
            ->get();

        $item = 1;
        $products = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->where('products.status_products', $item)
            ->orderBy('products.id_products', 'DESC')
            ->distinct()
            ->get();

        $productsHomes = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->where('products.status_products', $item)
            ->orderBy('products.id_products', 'DESC')
            ->distinct()
            ->limit(8)
            ->get();

        $blogs = DB::table('posts')
            ->join('users', 'users.id', '=', 'posts.posts_user_id')
            ->join('categories_blog', 'categories_blog.categories_blog_id', '=', 'posts.posts_category_id')
            ->where('posts.posts_status', '=', 1)
            ->orderBy('posts.posts_id', 'desc')
            ->distinct()
            ->get();

        $partenaires = DB::table('partenaires')
            ->where('status_partenaires', '=', 1)
            ->orderBy('partenaires.id_partenaires', 'desc')
            ->get();

        $realisations = DB::table('realisations')
            ->where('statut_realisations', '=', 1)
            ->where('isActive', '=', 1)
            ->orderBy('realisations.id_realisations', 'desc')
            ->distinct()
            ->limit(6)
            ->get();

        $reglages = DB::table('reglages')
            ->distinct()
            ->orderBy('reglages.id_reglages', 'desc')
            ->get();

        $Publicites = DB::table('publicite')
            ->distinct()
            ->orderBy('publicite.id_publicite', 'desc')
            ->get();

        $politique = DB::table('politique')
            ->distinct()
            ->get();

        $response = [
            'categories' => $categories,
            'sliders' => $sliders,
            'products' => $products,
            'productsHomes' => $productsHomes,
            'blogs' => $blogs,
            'partenaires' => $partenaires,
            'realisations' => $realisations,
            'reglages' => $reglages,
            'Publicites' => $Publicites,
            'politique' => $politique,
        ];

        return $this->apiResponse(200, "liste des donnée de la page HomePage", $response, 200);
    }

    // Service HomeService

    public function getAllPropriete()
    {
        $colors = DB::table('couleurs')
            ->where('couleurs.state_couleurs', '=', 1)
            ->distinct()
            ->get();

        $stores = DB::table('stores')
            ->orderBy('stores.id_stores', 'desc')
            ->get();

        $categories = DB::table('categories_produits')
            ->orderBy('categories_produits.id_categories_produits', 'desc')
            ->where('categories_produits.state_categories_produits', '=', 1)
            ->get();

        $size = DB::table('size')
            ->where('size.state_size', '=', 1)
            ->distinct()
            ->get();

        $pointures = DB::table('pointures')
            ->where('pointures.state_pointures', '=', 1)
            ->distinct()
            ->get();

        $response = [
            'colors' => $colors,
            'tailles' => $size,
            'pointures' => $pointures,
            'stores' => $stores,
            'categories' => $categories,
        ];

        return $this->apiResponse(200, "liste des Proprietes", $response, 200);
    }

    public function getDeatailSop($id)
    {
        $products = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->join('stores', 'stores.id_stores', '=', 'products.id_boutique')
            ->where('products.code_products', '=', $id)
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();

        $imgDta = DB::table('products')
            ->join('catalogue', 'catalogue.id_produits', '=', 'products.id_products')
            ->where('products.code_products', '=', $id)
            ->distinct()
            ->get();

        $caracteristiques = DB::table('caracteristiques')
            ->join('products', 'products.code_products', '=', 'caracteristiques.codeProd_caracteristiques')
            ->where('products.code_products', '=', $id)
            ->distinct()
            ->get();

        $Colors = DB::table('products')
            ->join('couleurs_produit', 'couleurs_produit.product_id', '=', 'products.id_products')
            ->join('couleurs', 'couleurs.id_couleurs', '=', 'couleurs_produit.couleurs_id')
            ->where('products.code_products', '=', $id)
            ->distinct()
            ->get(['id_couleurs', 'name_couleurs']);

        $size = DB::table('products')
            ->join('produit_size', 'produit_size.product_id', '=', 'products.id_products')
            ->join('size', 'size.id_size', '=', 'produit_size.size_id')
            ->where('products.code_products', '=', $id)
            ->distinct()
            ->get();

        $pointures = DB::table('products')
            ->join('pointures_produit', 'pointures_produit.product_id', '=', 'products.id_products')
            ->join('pointures', 'pointures.id_pointures', '=', 'pointures_produit.pointures_id')
            ->where('products.code_products', '=', $id)
            ->distinct()
            ->get(['id_pointures', 'name_pointures']);

        $parametreProduits = DB::table('products')
            ->join('parametre_produit', 'parametre_produit.id_produitparametre', '=', 'products.id_products')
            ->where('products.code_products', '=', $id)
            ->distinct()
            ->get();

        $parametreImages = DB::table('products')
            ->join('pagnes', 'pagnes.Id_produitpagnes', '=', 'products.id_products')
            ->where('products.code_products', '=', $id)
            ->distinct()
            ->get();

        $response = [
            'data' => $products,
            'imgDta' => $imgDta,
            'Colors' => $Colors,
            'tailles' => $size,
            'pointures' => $pointures,
            'caracteristiques' => $caracteristiques,
            'parametreProduits' => $parametreProduits,
            'parametreImages' => $parametreImages,
        ];

        return $this->apiResponse(200, "Consultation des données", $response, 200);
    }

    public function searchRange($valeur)
    {
        $products = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->where('products.price', '<=', $valeur)
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();

        $response = $products;
        return $this->apiResponse(200, "Consultation des données", $response, 200);
    }

    public function searchRangeByItem($valeur, $item)
    {
        $response = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->join('sous_categories_produits', 'sous_categories_produits.id_sous_categories_produits', '=', 'categoriesByproduits.sous_categoriesId')
            ->where('products.price', '<=', $valeur)
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();

        return $this->apiResponse(200, "Consultation des données", $response, 200);
    }

    public function searchProduct($valeur)
    {
        $response = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->where('products.name_product', 'like', '%' . $valeur . '%')
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();
        return $this->apiResponse(200, "Consultation des données", $response, 200);
    }

    public function selectRealisation($valeur)
    {
        $realisations = DB::table('realisations')
            ->join('op_realisation', 'op_realisation.idrealis_op_realisation', '=', 'realisations.id_realisations')
            ->join('option_reaalisation', 'option_reaalisation.id_option_reaalisation', '=', 'op_realisation.idoption_realis_op_realisation')
            ->where('op_realisation.idoption_realis_op_realisation', '=', $valeur)
            ->where('realisations.statut_realisations', '=', 1)
            ->orderBy('realisations.id_realisations', 'desc')
            ->distinct()
            ->paginate(50);
        return $this->apiResponse(200, "Consultation des données", $realisations, 200);
    }

    public function SelectOccasion($valeur)
    {
        $products = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->where('products.Occasion', '=', $valeur)
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();
        return $this->apiResponse(200, "Consultation des données", $products, 200);
    }

    public function searchProductByItem($valeur, $item)
    {
        $products = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->join('sous_categories_produits', 'sous_categories_produits.id_sous_categories_produits', '=', 'categoriesByproduits.sous_categoriesId')
            ->where('products.name_product', 'like', '%' . $valeur . '%')
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();

        return $this->apiResponse(200, "Consultation des données", $products, 200);
    }

    public function searchstores($valeur)
    {
        $datas = DB::table('stores')
            ->join('users', 'users.id', '=', 'stores.idUsers_stores')
            ->where('stores.nom_stores', 'like', '%' . $valeur . '%')
            ->orderBy('stores.id_stores', 'desc')
            ->get();
        return $this->apiResponse(200, "Consultation des données", $datas, 200);
    }


    // HomeService.php

    public function verifBoutique($valeur)
    {
        $stores = DB::table('stores')
            ->where('stores.nom_stores', 'like', '%' . $valeur . '%')
            ->orderBy('stores.id_stores', 'desc')
            ->distinct()
            ->get();

        $messages = "";
        $nb = count($stores);

        if ($nb > 0) {
            $messages = "Cette boutique existe déjà";
        } else {
            $messages = "Le nom de votre boutique est valide";
        }

        return $this->apiResponse(200, "Consultation des données", ['data' => $nb, 'messages' => $messages], 200);
    }

    public function verifEmail($valeur)
    {
        $stores = DB::table('users')
            ->where('users.email', 'like', '%' . $valeur . '%')
            ->orderBy('users.id', 'desc')
            ->distinct()
            ->get();

        $messages = "";
        $nb = count($stores);

        if ($nb > 0) {
            $messages = "Cette adresse existe déjà";
        } else {
            // No message defined
        }

        return $this->apiResponse(200, "Consultation des données", ['data' => $nb, 'messages' => $messages], 200);
    }

    public function saveStore(Request $request)
    {
        // Votre code d'insertion pour la boutique, la validation et l'envoi d'email
        $messages = "Boutique enregistrée avec succès";  // Ajoutez votre logique pour générer un message ici
        return $this->apiResponse(200, "Boutique enregistrée", ['success' => true, 'message' => $messages], 200);
    }

    public function savelivreurs(Request $request)
    {
        // Logique pour enregistrer le livreur
        $messages = "Livreur enregistré avec succès";  // Ajoutez votre logique pour générer un message ici
        return $this->apiResponse(200, "Livreur enregistré", ['success' => true, 'message' => $messages], 200);
    }

    public function Addsouhait(Request $request)
    {
        // Logique pour ajouter un produit à la liste de souhaits
        $messages = "Produit ajouté à la liste de souhaits avec succès";
        return $this->apiResponse(200, "Produit ajouté", ['success' => true, 'message' => $messages], 200);
    }

    public function getAllwishlistByUsers($id)
    {
        $DataWishlist = DB::table('wishlist')
            ->join('products', 'products.id_products', '=', 'wishlist.idProduits_wishlist')
            ->join('stores', 'stores.id_stores', '=', 'wishlist.storesId_wishlist')
            ->where('wishlist.idusers_wishlist', $id)
            ->orderBy('wishlist.wishlist_id', 'desc')
            ->distinct()
            ->get();

        return $this->apiResponse(200, "Liste des souhaits", $DataWishlist, 200);
    }

    public function getsouscategories($data)
    {
        $response = [];

        if ($data) {
            $tabdata = explode(",", $data);

            $subCategories = DB::table('sous_categories_produits')
                ->whereIn('sous_categories_produits.idcategories_produits', $tabdata)
                ->where('sous_categories_produits.state_sous_categories_produits', '=', 1)
                ->orderBy('sous_categories_produits.libelle_sous_categories_produits', 'ASC')
                ->distinct()
                ->get(['id_sous_categories_produits', 'libelle_sous_categories_produits']);

            $response = [
                'data' => $subCategories,
            ];
        }

        return $this->apiResponse(200, "Consultation des sous-catégories", $response, 200);
    }

    public function getSousSousCategories($data)
    {
        $response = [];

        if ($data) {
            $tabdata = explode(",", $data);

            $subCategories = DB::table('sous_sous_categories_produits')
                ->join('sous_categories_produits', 'sous_categories_produits.id_sous_categories_produits', '=', 'sous_sous_categories_produits.idsous_categories_produits')
                ->whereIn('sous_sous_categories_produits.idsous_categories_produits', $tabdata)
                ->where('sous_sous_categories_produits.state_sous_sous_categories_produits', '=', 1)
                ->orderBy('sous_sous_categories_produits.libelle_sous_sous_categories', 'ASC')
                ->distinct()
                ->get(['idsous_categories_produits', 'libelle_sous_sous_categories', 'id_sous_sous_categories_produits', 'libelle_sous_sous_categories']);

            $response = [
                'data' => $subCategories,
            ];
        }

        return $this->apiResponse(200, "Consultation des sous-sous-catégories", $response, 200);
    }

    public function getAllordersKanblan($id)
    {
        -$orders = DB::table('orders')
            ->orderBy('orders.id_orders', 'desc')
            ->distinct()
            ->get();

        return $this->apiResponse(200, "Consultation des commandes", $orders, 200);
    }

    public function getAllorders($filters)
    {
        // Récupérer les paramètres de filtrage
        $page = $filters['page'];
        $limit = $filters['limit'];
        $search = $filters['search'];
        // Construire la requête de base
        $query = DB::table('orders')->orderBy('orders.id_orders', 'desc')->distinct();
        // Ajouter un filtre de recherche sur le champ 'transaction_id' si un terme de recherche est fourni
        if ($search) {
            $query->where('orders.transaction_id', 'like', '%' . $search . '%');
        }
        // Ajouter la pagination en utilisant la méthode paginate de Laravel
        $orders = $query->paginate($limit, ['*'], 'page', $page);
        // Retourner la réponse API avec les commandes paginées
        return $this->apiResponse(200, "Consultation des commandes", $orders, 200);
    }


    public function getListeLivreurs()
    {
        $roles = "Livreurs";
        $users = DB::table('users')
            ->where('users.is_admin', '=', $roles)
            ->orderBy('users.id', 'desc')
            ->distinct()
            ->get();

        return $this->apiResponse(200, "Liste des livreurs", $users, 200);
    }

    public function searchCommandes($id)
    {
        $orders = DB::table('orders')
            ->where('orders.transaction_id', 'like', '%' . $id . '%')
            ->orderBy('orders.id_orders', 'desc')
            ->distinct()
            ->paginate(3);

        return $this->apiResponse(200, "Recherche de commandes", $orders, 200);
    }

    public function getAllordersByUsers($id)
    {
        $orders = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('users.id', '=', $id)
            ->orderBy('orders.id_orders', 'desc')
            ->distinct()
            ->get();

        $sommes = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('users.id', '=', $id)
            ->where('orders.status_orders', '=', 5)
            ->select(DB::raw('SUM(total) as total_pays'))
            ->get();

        $Users = DB::table('users')
            ->where('users.id', '=', $id)
            ->distinct()
            ->get();

        return $this->apiResponse(200, "Commande par utilisateur", ['data' => $orders, 'users' => $Users, 'sommes' => $sommes], 200);
    }

    public function getdetailCommandes($id)
    {
        $ordersData = DB::table('orders')
            ->join('achats', 'achats.orderId', '=', 'orders.id_orders')
            ->join('realisations', 'realisations.code_realisation', '=', 'achats.codeAchat')
            ->where('orders.id_orders', '=', $id)
            ->orderBy('achats.id_achats', 'desc')
            ->distinct()
            ->get();

        // $ordersData = DB::table('orders')
        // ->where('orders.id_orders', '=', $id)
        //     ->orderBy('orders.id_orders', 'desc')
        //     ->distinct()
        //     ->get();

        return $this->apiResponse(200, "Détail des commandes", $ordersData, 200);
    }

    public function getOrdersDetail($id, $users)
    {
        $orders = DB::table('orders')
            ->join('order_product', 'order_product.order_id', '=', 'orders.id_orders')
            ->join('products', 'products.id_products', '=', 'order_product.product_id')
            ->where('orders.id_orders', '=', $id)
            ->orderBy('order_product.order_id', 'desc')
            ->distinct()
            ->get();

        $ordersData = DB::table('orders')
            ->where('orders.id_orders', '=', $id)
            ->orderBy('orders.id_orders', 'desc')
            ->distinct()
            ->get();

        return $this->apiResponse(200, "Détail des produits de la commande", ['data' => $orders, 'ordersData' => $ordersData], 200);
    }


    public function addpartenaire(Request $request)
    {
        $today = Carbon::today();
        $relativePath = "";
        $data = "";
        $uploadedFiles = $request->file('pics');
        $usersid = $request->usersid;

        foreach ($request->file('files') as $file) {
            $dir = 'Partenaires/';
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $str1 = '0123456789';
            $shuffled = str_shuffle($str);
            $shuffled1 = str_shuffle($str1);
            $code = "partenaires-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
            $absolutePath = public_path($dir);
            $extension = $file->getClientOriginalExtension();
            $filename = $code . '.' . $extension;
            $file->move($absolutePath, $filename);
            $relativePath = $dir . $filename;
            $photo = $relativePath;

            $date = date('Y-m-d H:i:s');
            // Ajout notre partenaire
            $partenaires = DB::table('partenaires')->insertGetId([
                'libelle_partenaires' => $request->libelle_partenaires,
                'Path_partenaires' => $relativePath,
                'created_at' => $date,
            ]);
        }

        return $this->apiResponse(200, "Consultation des sous-sous-catégories", 'Partenaire ajouté avec succès', 200);
    }

    public function changestatPartenaires($id, $staus)
    {
        $donnes = DB::table('partenaires')
            ->where('id_partenaires', '=', $id)
            ->update([
                'status_partenaires' => $staus,
            ]);
        $messages = "Status mis à jour avec succès";
        return $this->apiResponse(200, "Consultation des sous-sous-catégories", $messages, 200);
    }

    public function getPartenaires($id)
    {
        $datas = DB::table('partenaires')
            ->orderBy('partenaires.id_partenaires', 'desc')
            ->paginate(4);
        return $this->apiResponse(200, "Consultation des sous-sous-catégories", $datas, 200);
    }

    public function listespartenaire($id)
    {
        $datas = DB::table('partenaires')
            ->where('status_partenaires', '=', 1)
            ->orderBy('partenaires.id_partenaires', 'desc')
            ->get();

        $response = [
            'datas' => $datas,
        ];
        return response()->json($response);
    }

    public function Addachats(Request $request)
    {
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str1 = '0123456789';
        $shuffled = str_shuffle($str);
        $shuffled1 = str_shuffle($str1);
        $code = "TRANS-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
        $date = date('Y-m-d H:i:s');
        $heurs = date('H:i:s');
        $modeDePaiement = "Paiement à la livraison";

        $Order = DB::table('orders')->insertGetId([
            'transaction_id' => $code,
            'email_orders' => $request->emailAchats,
            'Mode_paiement' =>  $modeDePaiement,
            'date_orders' =>  $date,
            'heurs_orders' =>  $heurs,
            'personnalise' =>  2,
            'contact_paiement' => $request->numeroAchats,
            'nomUsers_orders' => $request->NomPrenomAchats,
            'created_at' => $date,
        ]);

        if ($Order) {
            DB::table('achats')->insertGetId([
                'codeAchat' => $request->codeAchat,
                'orderId' =>  $Order,
                'dimensionAchats' => $request->dimensionAchats,
                'taillesParamsAchats' => $request->taillesParamsAchats,
                'pointuresParamsAchats' => $request->pointuresParamsAchats,
                'couleursAchats' => $request->couleursAchats,
                'numeroAchats' => $request->numeroAchats,
                'NomPrenomAchats' => $request->NomPrenomAchats,
                'EntrepriseAchats' => $request->EntrepriseAchats,
                'emailAchats' => $request->emailAchats,
                'FileAchats' => $request->FileAchats,
                'imgLogosAchats' => $request->imgLogosAchats,
                'policeAchats' => $request->policeAchats,
                'PositionsFiles' => $request->PositionsFiles,
                'texteAchats' => $request->texteAchats,
                'remarques' => $request->remarques,
                'created_at' => $date,
            ]);

            // Envoi de l'email à l'admin
            $hello = "Bonjour, Tarafé";
            $emailAdmin = "contact@tarafe.com";
            $message = "Vous avez une nouvelle commande de " . $request->NomPrenomAchats . ".... ,Connectez-vous à votre espace pour gérer cette nouvelle commande.";
            $condition = "Si vous rencontrez des difficultés, contactez-nous à l'adresse : " . $emailAdmin;

            $state = 3;
            $sujet = "Notification d'une nouvelle commande N° " . $code;
            $Users = "Equipe Tarafé";
            $maildata = [
                'state' => $state,
                'message' => $message,
                'condition' => $condition,
                'hello' => $hello,
                'sujet' => $sujet,
                'Users' => $Users,
                'emailAdmin' => $emailAdmin,
            ];
            Mail::to($emailAdmin)->send(new Notifications($maildata));

            $states = true;
            $response = [
                'data' => $states,
            ];

            return $this->apiResponse(200, "Notification  envoyer", $response, 200);
        }
    }

    public function Addlogosurl(Request $request)
    {
        $uploadedFiles = $request->logo;
        $dir = 'ImgesPerson/';
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str1 = '0123456789';
        $shuffled = str_shuffle($str);
        $shuffled1 = str_shuffle($str1);
        $code = "Com-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
        $absolutePath = public_path($dir);
        $extension = $uploadedFiles->getClientOriginalExtension();
        $filename = $code . '.' . $extension;
        $uploadedFiles->move($absolutePath, $filename);
        $relativePath = $dir . $filename;

        $response = [
            'data' => $relativePath,
        ];
        return $this->apiResponse(200, "", $response, 200);

        return response()->json($response);
    }

    public function addDevis(Request $request)
    {
        $uploadedFiles = $request->logo;
        $dir = 'ListeDevis/';
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str1 = '0123456789';
        $shuffled = str_shuffle($str);
        $shuffled1 = str_shuffle($str1);
        $code = "Devis-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
        $absolutePath = public_path($dir);
        $extension = $uploadedFiles->getClientOriginalExtension();
        $filename = $code . '.' . $extension;
        $uploadedFiles->move($absolutePath, $filename);
        $relativePath = $dir . $filename;

        DB::table('achats')
            ->where('orderId', '=', $request->orderId)
            ->update(['devisFiles' => $relativePath]);

        $response = [
            'data' => $relativePath,
        ];

        return response()->json($response);
    }

    public function addfactures(Request $request)
    {
        $uploadedFiles = $request->logo;
        $dir = 'Factures/';
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str1 = '0123456789';
        $shuffled = str_shuffle($str);
        $shuffled1 = str_shuffle($str1);
        $code = "fichiers-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
        $absolutePath = public_path($dir);
        $extension = $uploadedFiles->getClientOriginalExtension();
        $filename = $code . '.' . $extension;
        $uploadedFiles->move($absolutePath, $filename);
        $relativePath = $dir . $filename;

        if ($request->options == 0) {
            DB::table('achats')
                ->where('orderId', '=', $request->orderId)
                ->update([
                    'modelfiles' => $relativePath,
                    'state' => 4
                ]);
        } else {
            DB::table('achats')
                ->where('orderId', '=', $request->orderId)
                ->update([
                    'facturesFiles' => $relativePath,
                    'state' => 4
                ]);
        }

        $response = [
            'data' => $relativePath,
        ];

        return response()->json($response);
    }

    // Fonction pour passer une commande
    public function PlaceOrder($request)
    {
        $users = $request->id == 'null' ? 0 : $request->id;
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str1 = '0123456789';
        $shuffled = str_shuffle($str);
        $shuffled1 = str_shuffle($str1);
        $code = "TRANS-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
        $date = date('Y-m-d H:i:s');
        $heurs = date('H:i:s');

        $modeDePaiement = $request->modes == 1 ? "Paiement à la livraison" : "Paiement avec TouchPay";

        $Order = DB::table('orders')->insertGetId([
            'transaction_id' => $code,
            'adresse_paiement' => $request->adresse,
            'email_orders' => $request->email,
            'total' => $request->price,
            'Mode_paiement' => $modeDePaiement,
            'date_orders' => $date,
            'heurs_orders' => $heurs,
            'contact_paiement' => $request->phone,
            'nomUsers_orders' => $request->nomUsers,
            'user_id' => $users,
            'notes_orders' => $request->notescommande,
            'created_at' => $date,
        ]);

        if ($Order) {
            $newstoks = "";
            $theprod = "";

            $dataPanie = json_decode($request->cart);
            foreach ($dataPanie as $data) {
                DB::table('orders')
                    ->where('id_orders', '=', $Order)
                    ->update([
                        'couleur_orders' => $data->colors,
                        'taille_orders' => $data->taille,
                        'pointures_orders' => $data->pointures,
                    ]);

                // Insertion du détail de la commande
                DB::table('order_product')->insert([
                    'order_id' => $Order,
                    'product_id' => $data->id_products,
                    'stores_id' => $data->id_boutique,
                    'quantity' => $data->quantity,
                ]);

                $dataProd = DB::table('products')
                    ->where('products.id_products', '=', $data->id_products)
                    ->get();

                $theprod = json_decode($dataProd);
                $newstoks = $theprod[0]->stoks == 0 ? 0 : $theprod[0]->stoks - $data->quantity;

                DB::table('products')
                    ->where('products.id_products', '=', $data->id_products)
                    ->update([
                        'stoks' => $newstoks,
                    ]);

                // Commande personnalisée
                if ($data->pagnes || $data->textes || $data->logos || $data->valueradios) {
                    DB::table('personalization')->insertGetId([
                        'product_id' => $data->id_products,
                        'id_commandes_personalization' => $Order,
                        'numpagnes_personalization' => $data->pagnes,
                        'textes_personalization' => $data->textes,
                        'logos_personalization' => $data->logos,
                        'couleur_personalization' => $data->valueradios,
                        'created_at' => $date,
                    ]);
                }
            }

            $personnalise = DB::table('personalization')
                ->where('personalization.id_commandes_personalization', '=', $Order)
                ->get();

            if (count($personnalise) > 0) {
                $retour = json_decode($personnalise);
                $donnes = 1;

                DB::table('orders')
                    ->where('orders.id_orders', '=', $retour[0]->id_commandes_personalization)
                    ->update([
                        'personnalise' => $donnes,
                    ]);
            }
        }

        return $code;
    }

    // Ajouter un commentaire
    public function addcomments($request)
    {
        $status = "1";
        $today = date("Y-m-d");
        $news = DB::table('blog_comment')->insertGetId([
            'nom_blog_comment' => $request->nomPrenom,
            'email_blog_comment' => $request->email,
            'texte_blog_comment' => $request->contents,
            'status_blog_comment' => $status,
            'posteId_comment' => $request->posteId,
            'created_at' => $today,
        ]);

        return $this->apiResponse(200, "Commentaire ajouté avec succès", ['status' => $news ? "BON" : "Mauvais"], 200);
    }

    // Récupérer les réalisations par libellé
    public function getRealisationbyLibelle($id)
    {
        $donnes = DB::table('realisations')->where('libelle_realisations', '=', $id)->get();
        $codes = json_decode($donnes)[0]->code_realisation ?? '';
        $images = DB::table('img_realisations')->where('codeId', '=', $codes)->get();
        $reglages = DB::table('reglages')->distinct()->orderBy('reglages.id_reglages', 'desc')->get();
        return $this->apiResponse(200, "Consultation des réalisations par libellé", ['images' => $images, 'id' => $codes, 'realisations' => $donnes, 'reglages' => $reglages], 200);
    }

    // Récupérer les réalisations par ID
    public function getRealisationbyId($id)
    {
        $donnes = DB::table('realisations')->where('code_realisation', '=', $id)->get();
        $images = DB::table('img_realisations')->where('codeId', '=', $id)->get();
        return $this->apiResponse(200, "Consultation des réalisations par ID", [
            'images' => $images,
            'data' => $donnes,
        ], 200);
    }

    // Supprimer une image de la galerie
    public function deleteImagesGalleries($id)
    {
        DB::table('gallerie_images')
            ->where('id_gallerie_images', '=', $id)
            ->delete();

        return $this->apiResponse(200, "Image supprimée avec succès", [], 200);
    }

    // Supprimer une réalisation
    public function deleteRealisations($id)
    {
        DB::table('realisations')
            ->where('id_realisations', '=', $id)
            ->delete();

        DB::table('op_realisation')
            ->where('idrealis_op_realisation', '=', $id)
            ->delete();

        return $this->apiResponse(200, "Réalisation supprimée avec succès", [], 200);
    }

    // Changer le statut d'une réalisation
    public function changestatRealisations($id, $status)
    {
        DB::table('realisations')
            ->where('id_realisations', '=', $id)
            ->update(['statut_realisations' => $status]);

        return $this->apiResponse(200, "Statut mis à jour avec succès", [], 200);
    }

    public function isActivedChange($id, $status)
    {
        DB::table('realisations')
            ->where('id_realisations', '=', $id)
            ->update(['isActive' => $status]);

        return $this->apiResponse(200, "Statut mis à jour avec succès", [], 200);
    }


    public function getAllRealisationsBystatus($request)
    {


        if ($request == 0) {

            $realisations = DB::table('realisations')
                ->where('statut_realisations', '=', 1)
                ->orderBy('realisations.id_realisations', 'desc')
                ->distinct()
                ->paginate(30);
        } else {

            $realisations = DB::table('realisations')
                ->join('op_realisation', 'op_realisation.idrealis_op_realisation', '=', 'realisations.id_realisations')
                ->join('option_reaalisation', 'option_reaalisation.id_option_reaalisation', '=', 'op_realisation.idoption_realis_op_realisation')
                ->where('op_realisation.idoption_realis_op_realisation', '=', $request)
                ->where('realisations.statut_realisations', '=', 1)
                ->orderBy('realisations.id_realisations', 'desc')
                ->distinct()
                ->paginate(30);
        }

        // dd($realisations);

        $OptionRealisation = DB::table('option_reaalisation')
            ->where('stateOption_reaalisation', '=', 1)
            ->orderBy('option_reaalisation.id_option_reaalisation', 'desc')
            ->distinct()
            ->get();

        $reglages = DB::table('reglages')
            ->distinct()->orderBy('reglages.id_reglages', 'desc')
            ->get();

        $response = [

            'realisations' => $realisations,
            'reglages' => $reglages,
            'OptionRealisation' => $OptionRealisation,
        ];

        return $this->apiResponse(200, "Données récupérées avec succès", $response, 200);
    }


    public function addInput(Request $request)
    {
        $data = false;

        switch ($request->position) {
            case 1:
                DB::table('reglages')->where('id_reglages', '=', $request->id_reglages)->update(['description_reglages' => $request->description_reglages]);
                $data = true;
                break;
            case 2:
                DB::table('reglages')->where('id_reglages', '=', $request->id_reglages)->update(['desc_footer' => $request->desc_footer]);
                $data = true;
                break;
                // Ajouter les autres cas ici de manière similaire
        }

        return $this->apiResponse(200, "Données mises à jour avec succès", $data, 200);
    }


    public function AddMultifiles(Request $request)
    {
        $uploadedFiles = $request->logo;
        $dir = ($request->positionfiles == 4 || $request->positionfiles == 5) ? 'PubCard/' : 'Logos/';

        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str1 = '0123456789';
        $shuffled = str_shuffle($str);
        $shuffled1 = str_shuffle($str1);
        $code = "images-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
        $absolutePath = public_path($dir);
        $extension = $uploadedFiles->getClientOriginalExtension();
        $filename = $code . '.' . $extension;
        $uploadedFiles->move($absolutePath, $filename);
        $relativePath = $dir . $filename;

        $data = false;
        switch ($request->positionfiles) {
            case 1:
                DB::table('reglages')->where('id_reglages', '=', $request->id_reglages)->update(['logoSite_reglages' => $relativePath]);
                $data = true;
                break;
                // Ajouter les autres cas ici de manière similaire
        }

        return $this->apiResponse(200, "Fichier téléchargé et mis à jour avec succès", $data, 200);
    }


    public function profilUpdate(Request $request)
    {
        DB::table('equipes')
            ->where('id_equipe', '=', $request->id_equipe)
            ->update([
                'nomPren_equipe' => $request->nomPren_equipe,
                'fonction_equipe' => $request->fonction_equipe,
                'email_equipe' => $request->email_equipe,
            ]);

        return $this->apiResponse(200, "Profil mis à jour avec succès", [], 200);
    }


    public function SaveDataTitle(Request $request)
    {
        DB::table('reglages')
            ->where('id_reglages', '=', $request->id_reglages)
            ->update([
                'texteHeader1' => $request->texteHeader1,
                'texteHeader2' => $request->texteHeader2,
            ]);

        return $this->apiResponse(200, "Titre mis à jour avec succès", [], 200);
    }

    public function getPositions()
    {
        $res = DB::table('realisations')->orderBy('realisations.id_realisations', 'desc')->first();
        $realisation = $res->position;

        return $this->apiResponse(200, "Position récupérée avec succès", $realisation, 200);
    }


    public function Addpolitique(Request $request)
    {
        $today = date("Y-m-d");
        if ($request->id_politique) {
            $politique = DB::table('politique')
                ->where('politique.id_politique', $request->id_politique)
                ->update([
                    'libelle_politique' => $request->libelle_politique,
                    'description_politique' => $request->description_politique,
                ]);
        } else {
            $politique = DB::table('politique')->insertGetId([
                'libelle_politique' => $request->libelle_politique,
                'description_politique' => $request->description_politique,
                'created_at' => $today,
            ]);
        }

        return $this->apiResponse(200, "Politique enregistrée avec succès", $politique, 200);
    }


    public function getAllpolitique()
    {
        $politique = DB::table('politique')
            ->distinct()
            ->get();

        return $this->apiResponse(200, "Politiques récupérées avec succès", $politique, 200);
    }


    public function getreglages()
    {
        $reglages = DB::table('reglages')->distinct()->orderBy('reglages.id_reglages', 'desc')->get();
        $getEquipes = DB::table('equipes')->distinct()->orderBy('equipes.id_equipe', 'desc')->get();
        $response = [
            'reglages' => $reglages,
            'equipes' => $getEquipes,
        ];

        return $this->apiResponse(200, "Réglages récupérés avec succès", $response, 200);
    }


    public function getAllpubs()
    {
        $getAllpub = DB::table('publicite')
            ->distinct()
            ->orderBy('publicite.id_publicite', 'desc')
            ->get();

        return $this->apiResponse(200, "Publicités récupérées avec succès", $getAllpub, 200);
    }

    public function getEquipe()
    {
        $getEquipes = DB::table('equipes')
            ->distinct()
            ->orderBy('equipes.id_equipe', 'desc')
            ->get();

        return $this->apiResponse(200, "Équipes récupérées avec succès", $getEquipes, 200);
    }


    public function getAllRealisations($filters)
    {
        // Récupérer les paramètres de filtrage
        $page = $filters['page'];
        $limit = $filters['limit'];
        $search = $filters['search'];
        // Construire la requête de base
        $query = DB::table('realisations')->orderBy('realisations.id_realisations', 'desc')->distinct();
        if ($search) {
            $query->where('realisations.libelle_realisations', 'like', '%' . $search . '%');
        }
        // Ajouter la pagination en utilisant la méthode paginate de Laravel
        $realisations = $query->paginate($limit, ['*'], 'page', $page);
        // Retourner la réponse API avec les realisations paginées
        // $realisations = DB::table('realisations')->distinct()->orderBy('realisations.id_realisations', 'desc')->paginate(20);
        return $this->apiResponse(200, "Réalisations récupérées avec succès", $realisations, 200);
    }

    public function getAllSubscribers()
    {
        $Subscribers = DB::table('newsletter')
            ->distinct()
            ->paginate(4);

        return $this->apiResponse(200, "Abonnés récupérés avec succès",  $Subscribers, 200);
    }


    public function getAllimgRealisations($id)
    {
        $images = DB::table('img_realisations')
            ->where('img_realisations.realisations_id', '=', $id)
            ->get();

        return $this->apiResponse(200, "Images récupérées avec succès", $images, 200);
    }


    public function removeImagesRealisation($id_img_realisations, $realisations_id)
    {
        DB::table('img_realisations')
            ->where('img_realisations.id_img_realisations', '=', $id_img_realisations)
            ->delete();

        $remainingImages = DB::table('img_realisations')
            ->where('realisations_id', '=', $realisations_id)
            ->get();

        return $this->apiResponse(200, "Image supprimée avec succès", $remainingImages, 200);
    }


    public function removeRealisation($id)
    {
        DB::table('realisations')
            ->where('id_realisations', '=', $id)
            ->delete();

        return $this->apiResponse(200, "Réalisations supprimées avec succès", [], 200);
    }


    public function SaveAllImages(Request $request)
    {
        $today = date("Y-m-d");
        $relativePaths = "";
        $status = 0;
        $extension = "";

        foreach ($request->file('files') as $fichiers) :
            $dir = 'Realisations/';
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $str1 = '0123456789';
            $shuffled = str_shuffle($str);
            $shuffled1 = str_shuffle($str1);
            $code = "Tarafes-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
            $absolutePath = public_path($dir);
            $extension = $fichiers->getClientOriginalExtension();
            $filename = $code . '.' . $extension;
            $fichiers->move($absolutePath, $filename);
            $relativePaths = $dir . $filename;
            $photo = $relativePaths;

            DB::table('img_realisations')->insert([
                'realisations_id' => $request->id_realisation,
                'filles_img_realisations' => $relativePaths,
                'one_img_realisations' => $request->one_img_realisations,
                'codeId' => $request->code_realisation,
                'created_at' => $today,
            ]);
        endforeach;

        return $this->apiResponse(200, "Document ajouté avec succès", ['files' => $relativePaths], 200);
    }

    public function getAllgallerieImages($filters)
    {
           // Récupérer les paramètres de filtrage
           $page = $filters['page'];
           $limit = $filters['limit'];
           $search = $filters['search'];

        $images = DB::table('gallerie_images')
            ->distinct()
            ->orderBy('gallerie_images.id_gallerie_images', 'desc')
            ->paginate(50);

        $reglages = DB::table('reglages')
            ->distinct()->orderBy('reglages.id_reglages', 'desc')
            ->get();

        $response = [
            'data' => $images,
            'reglages' => $reglages,
        ];

        return $this->apiResponse(200, "Images récupérées avec succès", $response, 200);
    }

    public function SavegallerieImages(Request $request)
    {
        $today = date("Y-m-d");
        $relativePaths = "";
        $extension = "";

        foreach ($request->file('files') as $fichiers) :
            $dir = 'GallerieImages/';
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $str1 = '0123456789';
            $shuffled = str_shuffle($str);
            $shuffled1 = str_shuffle($str1);
            $code = "Tarafes-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
            $absolutePath = public_path($dir);
            $extension = $fichiers->getClientOriginalExtension();
            $filename = $code . '.' . $extension;
            $fichiers->move($absolutePath, $filename);
            $relativePaths = $dir . $filename;

            DB::table('gallerie_images')->insert([
                'libelle_gallerie_images' => $request->libelle_gallerie_images,
                'files_gallerie_images' => $relativePaths,
                'created_at' => $today,
            ]);
        endforeach;

        return $this->apiResponse(200, "Document ajouté avec succès", ['files' => $relativePaths], 200);
    }

    public function SaveRealisations(Request $request)
    {
        $today = date("Y-m-d");
        $relativePath = "";
        $status = 0;
        $extension = "";
        $line1 = 'Abcdefghijklmnopqrstuvwxyz';
        $line2 = '0123456789';
        $composition1 = str_shuffle($line1);
        $composition2 = str_shuffle($line2);
        $code_Realisations = substr($composition1, 0, 5) . "" . substr($composition2, 0, 1);

        $uploadedFiles = $request->fileData;
        $dir = 'Realisations/';
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str1 = '0123456789';
        $shuffled = str_shuffle($str);
        $shuffled1 = str_shuffle($str1);
        $code = "Tarafe-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
        $absolutePath = public_path($dir);
        $extension = $uploadedFiles->getClientOriginalExtension();
        $filename = $code . '.' . $extension;
        $uploadedFiles->move($absolutePath, $filename);
        $relativePath = $dir . $filename;

        $realisationsId = DB::table('realisations')->insertGetId([
            'libelle_realisations' => $request->libelle,
            'statut_realisations' => $status,
            'descript_real' => $request->description,
            'position' => $request->positions,
            'users_realisations' => $request->usersid,
            'images_realisations' => $relativePath,
            'code_realisation' => $code_Realisations,
            'created_at' => $today,
        ]);

        $imgrealisations = DB::table('img_realisations')->insertGetId([
            'codeId' => $code_Realisations,
            'realisations_id' => $realisationsId,
            'filles_img_realisations' => $relativePath,
            'created_at' => $today,
        ]);

        if ($request->selected) {
            $tabselected = explode(",", $request->selected);
            foreach ($tabselected as $item) {
                DB::table('op_realisation')->insert([
                    'idoption_realis_op_realisation' => $item,
                    'idrealis_op_realisation' => $realisationsId,
                ]);
            }
        }

        return $this->apiResponse(200, "Réalisations ajoutées avec succès", ['realisationsId' => $realisationsId, 'tabselected' => $tabselected ?? [],], 200);
    }

    public function updateRealisations(Request $request)
    {
        $today = date("Y-m-d");
        $relativePath = "";
        $status = 0;
        $extension = "";
        $realisationsId = "";

        if ($request->states == 1) {
            $uploadedFiles = $request->fileData;
            $dir = 'Realisations/';
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $str1 = '0123456789';
            $shuffled = str_shuffle($str);
            $shuffled1 = str_shuffle($str1);
            $code = "Tarafe-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
            $absolutePath = public_path($dir);
            $extension = $uploadedFiles->getClientOriginalExtension();
            $filename = $code . '.' . $extension;
            $uploadedFiles->move($absolutePath, $filename);
            $relativePath = $dir . $filename;

            $realisationsId = DB::table('realisations')
                ->where('id_realisations', $request->id_realisations)
                ->update([
                    'libelle_realisations' => $request->libelle,
                    'descript_real' => $request->description,
                    'users_realisations' => $request->usersid,
                    'images_realisations' => $relativePath,
                ]);
        } else if ($request->states == 0) {
            $realisationsId = DB::table('realisations')
                ->where('id_realisations', $request->id_realisations)
                ->update([
                    'libelle_realisations' => $request->libelle,
                    'descript_real' => $request->description,
                    'users_realisations' => $request->usersid,
                ]);
        }

        return $this->apiResponse(200, "Réalisations mises à jour avec succès", $realisationsId, 200);
    }

    public function addMessages($request)
    {
        $today = date("Y-m-d");

        $signatureId = DB::table('messages')->insertGetId([
            'email' => $request->email,
            'objet' => $request->objet,
            'messages' => $request->messages,
            'signature_messages' => $request->signature,
            'files_messages' => $request->files_messages,
            'created_at' => $today,
        ]);

        // Envoi de l'email
        $emailAdmin = "contact@tarafe.com";
        $condition = "Si vous rencontrez des difficultés, contactez-nous à l'adresse : " . $emailAdmin;
        $mailData = [
            'state' => 5,
            'message' => $request->messages,
            'condition' => $condition,
            'sujet' => $request->objet,
            'signature_messages' => $request->signature,
            'emailAdmin' => $emailAdmin,
            'url' => $request->files_messages,
        ];

        Mail::to($request->email)->send(new Notifications($mailData));

        return $this->apiResponse(200, "Message envoyé avec succès", ['message_id' => $signatureId], 200);
    }

    public function AddSignature($request)
    {
        $today = date("Y-m-d");

        if ($request->id_signature > 0) {
            DB::table('signature')->where('id_signature', $request->id_signature)
                ->update(['signature' => $request->signature]);
            return $this->apiResponse(200, "Signature mise à jour avec succès", null, 200);
        } else {
            $signatureId = DB::table('signature')->insertGetId([
                'signature' => $request->signature,
                'created_at' => $today,
            ]);
            return $this->apiResponse(200, "Signature ajoutée avec succès", ['signature_id' => $signatureId], 200);
        }
    }

    public function getAllsignature()
    {
        $signatures = DB::table('signature')->orderBy('id_signature', 'desc')->get();
        return $this->apiResponse(200, "Signatures récupérées avec succès", $signatures, 200);
    }

    public function subscribers($request)
    {
        $today = date("Y-m-d");
        $news = DB::table('newsletter')->insertGetId([
            'email' => $request->email,
            'created_at' => $today,
        ]);

        $message = $news ? "Inscription réussie à la newsletter" : "Échec de l'inscription à la newsletter";
        return $this->apiResponse(200, $message, null, 200);
    }

    public function AddVisites($request)
    {
        $today = date("Y-m-d");
        $visitId = DB::table('visites')->insertGetId([
            'ip_visites' => $request->ip_visites,
            'country_code_visites' => $request->country_code_visites,
            'country_name_visites' => $request->country_name_visites,
            'region_code_visites' => $request->region_code_visites,
            'region_name_visites' => $request->region_name_visites,
            'city_visites' => $request->city_visites,
            'heur_visites' => $request->heur_visites,
            'date_visites' => $request->date_visites,
            'latitude_visites' => $request->latitude_visites,
            'longitude_visites' => $request->longitude_visites,
        ]);

        return $this->apiResponse(200, "Visite enregistrée avec succès", $visitId, 200);
    }

    public function addnewletter($request)
    {
        $today = date("Y-m-d");
        $newsletterId = DB::table('newsletters')->insertGetId([
            'nom_newsletters' => $request->nomPrenom,
            'email_newsletters' => $request->email,
            'contact_newsletters' => $request->phone,
            'texte_newsletters' => $request->contents,
            'status_newsletters' => 1, // actif
            'created_at' => $today,
        ]);

        $message = $newsletterId ? "Inscription à la newsletter réussie" : "Échec de l'inscription à la newsletter";
        return $this->apiResponse(200, $message, $newsletterId, 200);
    }

    public function getAllnewsletters()
    {
        $newsletters = DB::table('newsletters')->orderBy('newsletters_id', 'DESC')->distinct()->paginate(6);
        return $this->apiResponse(200, "Liste des newsletters récupérées avec succès", $newsletters, 200);
    }

    public function employees()
    {
        $employees = DB::table('employees')->get();

        return $this->apiResponse(200, "Liste des employés récupérée avec succès", $employees, 200);
    }

    public function getAllCommenteById($id)
    {
        $blogs = DB::table('blog_comment')
            ->where('status_blog_comment', '=', 1)
            ->where('posteId_comment', '=', $id)
            ->get();

        return $this->apiResponse(200, "Commentaires du blog récupérés avec succès", $blogs, 200);
    }

    public function getStoreById($id)
    {
        $stores = DB::table('stores')
            ->join('users', 'users.id', '=', 'stores.idUsers_stores')
            ->where('stores.status_stores', '=', 1)
            ->where('stores.id_stores', '=', $id)
            ->get();

        return $this->apiResponse(200, "Magasins récupérés avec succès", $stores, 200);
    }

    public function addproduitscomments(Request $request)
    {
        $status = "1";
        $messages = "";
        $today = date("Y-m-d");

        $commentId = DB::table('produit_comment')->insertGetId([
            'nom_produit_comment' => $request->nomPrenom,
            'email_produit_comment' => $request->email,
            'texte_produit_comment' => $request->contents,
            'status_produit_comment' => $status,
            'produitCode_comment' => $request->posteId,
            'created_at' => $today,
        ]);

        if ($commentId) {
            $messages = "Commentaire ajouté avec succès";
        } else {
            $messages = "Erreur lors de l'ajout du commentaire";
        }

        return $this->apiResponse(200, $messages, $commentId, 200);
    }

    public function getAllproduitscommentsById($id)
    {
        $comments = DB::table('produit_comment')
            ->where('status_produit_comment', '=', 1)
            ->where('produitCode_comment', '=', $id)
            ->get();

        return $this->apiResponse(200, "Commentaires du produit récupérés avec succès", $comments, 200);
    }
}
