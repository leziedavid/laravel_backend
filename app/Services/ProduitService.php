<?php

namespace App\Services;  // La déclaration du namespace doit être la première ligne

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\Notifications;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\FuncCall;

class ProduitService
{
    use ApiResponse;


    public function getAllproduisByStores($id)
    {
        $usersDatas = DB::table('users')
            ->join('stores', 'stores.idUsers_stores', '=', 'users.id')
            ->where('users.id', '=', $id)->get();

        $idBoutiques = 0;
        $theDatas = json_decode($usersDatas);
        $idBoutiques = $theDatas[0]->id_stores;

        $products = DB::table('products')
            ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
            ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
            ->join('stores', 'stores.id_stores', '=', 'products.id_boutique')
            ->where('products.id_boutique', '=', $idBoutiques)->orderBy('products.id_products', 'desc')->distinct()->paginate(6);
        return $this->apiResponse(200, "Produits récupérés avec succès", $products, 200);
    }

    public function searchProductbyDateByStores($date1, $date2, $id)
    {
        $usersDatas = DB::table('users')
            ->join('stores', 'stores.idUsers_stores', '=', 'users.id')
            ->where('users.id', '=', $id)
            ->get();

        $idBoutiques = 0;
        $theDatas = json_decode($usersDatas);
        $idBoutiques = $theDatas[0]->id_stores;

        $products = DB::table('products')
            ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
            ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
            ->whereBetween('products.created_at', [$date1, $date2])
            ->where('products.id_boutique', '=', $idBoutiques)
            ->get();

        return $this->apiResponse(200, "Produits trouvés par date avec succès", $products, 200);
    }

    public function searchProductByStoresId($valeur, $id)
    {
        $usersDatas = DB::table('users')
            ->join('stores', 'stores.idUsers_stores', '=', 'users.id')
            ->where('users.id', '=', $id)
            ->get();

        $idBoutiques = 0;
        $theDatas = json_decode($usersDatas);
        $idBoutiques = $theDatas[0]->id_stores;

        $products = DB::table('products')
            ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
            ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
            ->where('products.id_boutique', '=', $idBoutiques)
            ->where('products.name_product', 'like', '%' . $valeur . '%')
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();

        return $this->apiResponse(200, "Produits trouvés par recherche avec succès", $products, 200);
    }

    public function getAllordersStores($id)
    {
        $usersDatas = DB::table('users')
            ->join('stores', 'stores.idUsers_stores', '=', 'users.id')
            ->where('users.id', '=', $id)
            ->get();

        $idBoutiques = 0;
        $theDatas = json_decode($usersDatas);
        $idBoutiques = $theDatas[0]->id_stores;

        $orders = DB::table('orders')
            ->join('order_product', 'order_product.order_id', '=', 'orders.id_orders')
            ->join('stores', 'stores.id_stores', '=', 'order_product.stores_id')
            ->where('stores.id_stores', '=', $idBoutiques)
            ->orderBy('orders.id_orders', 'desc')
            ->distinct()
            ->paginate(4);

        return $this->apiResponse(200, "Commandes récupérées avec succès", $orders, 200);
    }

    public function index($id)
    {
        $products = DB::table('products')
            ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
            ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
            ->join('stores', 'stores.id_stores', '=', 'products.id_boutique')
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->paginate(8);

        $response = [
            'datas' => $products,
        ];

        return $this->apiResponse(200, "Produits récupérés avec succès", $products, 200);
    }

    public function getAllproduisByState($id)
    {
        $faibles = DB::table('products')
            ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
            ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
            ->join('stores', 'stores.id_stores', '=', 'products.id_boutique')
            ->where('products.stoks', '=', 2)
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();

        $rupture = DB::table('products')
            ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
            ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
            ->join('stores', 'stores.id_stores', '=', 'products.id_boutique')
            ->where('products.stoks', '=', 0)
            ->orderBy('products.id_products', 'desc')
            ->distinct()
            ->get();

        $response = [
            'faibles' => $faibles,
            'rupture' => $rupture,
        ];

        return $this->apiResponse(200, "Produits par état récupérés avec succès", $response, 200);
    }

    public function getAllcategoriesBySearche($id)
    {
        $datas = DB::table('categories_produits')
            ->orderBy('categories_produits.id_categories_produits', 'desc')
            ->distinct()
            ->get();

        return $this->apiResponse(200, "Catégories récupérées avec succès", $datas, 200);
    }


    public function getAllcategories($id)
    {
        $datas = DB::table('categories')
        ->orderBy('categories.id_categories', 'desc')
        ->distinct()
            ->paginate(6);

        $item = 1;
        $boutiques = DB::table('stores')
        ->where('stores.status_stores', $item)
            ->get();

        $response = [
            'data' => $datas,
            'boutiques' => $boutiques,
        ];

        return $this->apiResponse(200, "Categories récupérées avec succès", $response, 200);
    }

    public function getAllcategoriesByProduits($id)
    {
        $datas = DB::table('categories_produits')
        ->orderBy('categories_produits.id_categories_produits', 'desc')
        ->distinct()
            ->paginate(3);

        return $this->apiResponse(200, "Catégories produits récupérées avec succès", $datas, 200);
    }

    public function selectedSouscategories($id)
    {
        $datas = DB::table('sous_categories_produits')
        ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'sous_categories_produits.idcategories_produits')
        ->where('sous_categories_produits.idcategories_produits', $id)
            ->orderBy('sous_categories_produits.id_sous_categories_produits', 'desc')
            ->paginate(6);

        $categories = DB::table('categories_produits')
        ->orderBy('categories_produits.id_categories_produits', 'desc')
        ->get();

        $response = [
            'datas' => $datas,
            'categories' => $categories,
        ];

        return $this->apiResponse(200, "Sous-catégories sélectionnées récupérées avec succès", $response, 200);
    }

    public function getAllsouscategorieIndex($id)
    {
        $datas = DB::table('sous_categories_produits')
        ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'sous_categories_produits.idcategories_produits')
        ->orderBy('sous_categories_produits.id_sous_categories_produits', 'desc')
        ->paginate(6);

        $categories = DB::table('categories_produits')
        ->orderBy('categories_produits.id_categories_produits', 'desc')
        ->get();

        $response = [
            'datas' => $datas,
            'categories' => $categories,
        ];

        return $this->apiResponse(200, "Sous-catégories index récupérées avec succès", $response, 200);
    }

    public function editsousCatégories($id, $idcategories)
    {
        $data = DB::table('sous_categories_produits')
        ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'sous_categories_produits.idcategories_produits')
        ->where('sous_categories_produits.id_sous_categories_produits', $id)->get();
        return $this->apiResponse(200, "Sous-catégorie modifiée récupérée avec succès", $data, 200);
    }

    public function getOptionRealisationByState()
    {
        $datas = DB::table('option_reaalisation')->where('option_reaalisation.stateOption_reaalisation', '=', 1)
        ->get();

        return $this->apiResponse(200, "Options de réalisation récupérées avec succès", $datas, 200);
    }

    public function getOpRealisation($id)
    {
        $datas = DB::table('op_realisation')->where('idrealis_op_realisation', $id) ->get();
        return $this->apiResponse(200, "Option de réalisation récupérée avec succès", $datas, 200);
    }

    public function getAllOptionRealisation($filters)
    {

        // Récupérer les paramètres de filtrage
        $page = $filters['page'] ?? 1; // Défaut à 1 si aucun paramètre de page n'est fourni
        $limit = $filters['limit'] ?? 10; // Défaut à 6 éléments par page
        $search = $filters['search'] ?? ''; // Le terme de recherche, vide si aucun

        // Construire la requête de base pour les newsletters
        $query = DB::table('option_reaalisation')->orderBy('option_reaalisation.id_option_reaalisation', 'DESC')->distinct();
        // Ajouter un filtre de recherche sur le champ 'objets' ou 'nom_newsletters' si un terme de recherche est fourni
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('libelleOption_reaalisation', 'like', '%' . $search . '%');
            });
        }
        // Appliquer la pagination en utilisant les paramètres de page et de limite
        $datas = $query->paginate($limit, ['*'], 'page', $page);
        return $this->apiResponse(200, "Toutes les options de réalisation récupérées avec succès", $datas, 200);
    }

    public function deleteOptionRealisation($id)
    {
        $donnes = DB::table('option_reaalisation')
        ->where('option_reaalisation.id_option_reaalisation', '=', $id)
            ->delete();
        return $this->apiResponse(200, "Option supprimées avec succès", [], 200);
    }

    public function changeStateOptionRealisation($id, $staus)
    {
        $donnes = DB::table('option_reaalisation')
        ->where('id_option_reaalisation', '=', $id)
        ->update([ 'stateOption_reaalisation' => $staus,]);
        return $this->apiResponse(200, "Statut de l'option de réalisation mis à jour avec succès",[], 200);
    }

    public function editOptionRealisation($id)
    {
        $data = DB::table('option_reaalisation')->where('id_option_reaalisation', '=', $id)->get();
        return $this->apiResponse(200, "Option de réalisation modifiée récupérée avec succès", $data, 200);
    }


    // mise a jour et creation de mes category

    public function SaveCategory(Request $request)
    {
        $today = Carbon::today();

        // Récupérer les catégories envoyées dans le body de la requête
        $categories = $request->categories;

        // Insertion de chaque catégorie dans la table option_reaalisation
        foreach ($categories as $category) {
            DB::table('option_reaalisation')->insert([ 'libelleOption_reaalisation' => $category,'created_at' => $today,]);
        }
        return $this->apiResponse(200, "Catégories ajoutées avec succès", [], 200);
    }

    public function updateCategory(Request $request, $id)
    {
        $today = Carbon::today();
    
        // Récupérer les catégories envoyées par l'API
        $categories = $request->input('categories');
    
        if (empty($categories) || !is_array($categories)) {
            return $this->apiResponse(400, "Aucune catégorie valide fournie", [], 400);
        }
    
        // Parcours des catégories et mise à jour dans la base de données
        foreach ($categories as $categoryName) {
            // Mise à jour de la catégorie spécifiée par son ID
            $updated = DB::table('option_reaalisation')
                ->where('id_option_reaalisation', '=', $id)
                ->update([
                    'libelleOption_reaalisation' => $categoryName,
                    'updated_at' => $today,
                ]);
    
            if (!$updated) {
                return $this->apiResponse(400, "Échec de la mise à jour pour la catégorie avec l'ID: $id", [], 400);
            }
        }
    
        return $this->apiResponse(200, "Catégorie(s) mise(s) à jour avec succès", [], 200);
    }
    


    // public function SaveOptionRealisation(Request $request)
    // {
    //     $today = Carbon::today();

    //     $data = DB::table('option_reaalisation')->insertGetId([
    //         'libelleOption_reaalisation' => $request->libelle,
    //         'created_at' => $today,
    //     ]);

    //     return $this->apiResponse(200, "Option de réalisation créée avec succès",[], 200);
    // }

    // public function updateOptionRealisation(Request $request)
    // {
    //     $today = Carbon::today();

    //     $donnes = DB::table('option_reaalisation')
    //     ->where('id_option_reaalisation', '=', $request->id_option_realisation)
    //         ->update([
    //             'libelleOption_reaalisation' => $request->libelle,
    //         ]);
    //     return $this->apiResponse(200, "Option de réalisation mise à jour avec succès", [], 200);
    // }

    // Paramètre des couleurs
    public function getAllCouleurs($id)
    {
        $datas = DB::table('couleurs')
        ->orderBy('couleurs.id_couleurs', 'desc')
        ->paginate(4);

        return $this->apiResponse(200, "Couleurs récupérées avec succès",$datas, 200);
    }

    public function deleteCouleurs($id)
    {
        DB::table('couleurs')
        ->where('couleurs.id_couleurs', '=', $id)
            ->delete();

        return $this->apiResponse(200, "Couleur supprimée avec succès", [], 200);
    }

    public function changesstatCouleurs($id, $staus)
    {
        DB::table('couleurs')
        ->where('id_couleurs', '=', $id)
            ->update(['state_couleurs' => $staus]);

        return $this->apiResponse(200, "Statut mis à jour avec succès", [], 200);
    }

    public function editCouleurs($id)
    {
        $data = DB::table('couleurs')
        ->where('couleurs.id_couleurs', $id)
            ->get();

        return $this->apiResponse(200, "Couleur récupérée avec succès",$data, 200);
    }

    public function SaveCouleurs(Request $request)
    {
        $today = Carbon::today();

        DB::table('couleurs')->insertGetId([
            'name_couleurs' => $request->libelle,
            'terme_couleurs' => $request->libelle,
            'created_at' => $today,
        ]);

        return $this->apiResponse(200, "Couleur créée avec succès", [], 200);
    }

    public function updateCouleurs(Request $request)
    {
        DB::table('couleurs')
        ->where('id_couleurs', '=', $request->id_couleur)
            ->update([
                'name_couleurs' => $request->libelle,
                'terme_couleurs' => $request->libelle,
            ]);

        return $this->apiResponse(200, "Couleur mise à jour avec succès", [], 200);
    }

    // Paramètre des tailles
    public function getAlltailles($id)
    {
        $datas = DB::table('size')
        ->orderBy('size.id_size', 'desc')
        ->paginate(4);

        return $this->apiResponse(200, "Tailles récupérées avec succès",$datas, 200);
    }

    public function deleteTailles($id)
    {
        DB::table('size')
        ->where('size.id_size', '=', $id)
            ->delete();

        return $this->apiResponse(200, "Taille supprimée avec succès", [], 200);
    }

    public function changesstatTailles($id, $staus)
    {
        DB::table('size')
        ->where('id_size', '=', $id)->update(['state_size' => $staus]);

        return $this->apiResponse(200, "Statut mis à jour avec succès", [], 200);
    }

    public function editTailles($id)
    {
        $data = DB::table('size')
        ->where('size.id_size', $id)
            ->get();

        return $this->apiResponse(200, "Taille récupérée avec succès", $data, 200);
    }

    public function SaveTailles(Request $request)
    {
        $today = Carbon::today();

        DB::table('size')->insertGetId([
            'name_size' => $request->libelle,
            'created_at' => $today,
        ]);

        return $this->apiResponse(200, "Taille créée avec succès", [], 200);
    }

    public function updateTailles(Request $request)
    {
        DB::table('size')
        ->where('id_size', '=', $request->id_taille)
            ->update(['name_size' => $request->libelle]);

        return $this->apiResponse(200, "Taille mise à jour avec succès", [], 200);
    }

    // Paramètre des pointures
    public function getAllpointures($id)
    {
        $datas = DB::table('pointures')
        ->orderBy('pointures.id_pointures', 'desc')
        ->paginate(4);

        return $this->apiResponse(200, "Pointures récupérées avec succès",$datas, 200);
    }

    public function deletePointures($id)
    {
        DB::table('pointures')
        ->where('pointures.id_pointures', '=', $id)
            ->delete();

        return $this->apiResponse(200, "Pointure supprimée avec succès", [], 200);
    }

    public function changesstatPointures($id, $staus)
    {
        DB::table('pointures')
        ->where('id_pointures', '=', $id)
            ->update(['state_pointures' => $staus]);

        return $this->apiResponse(200, "Statut mis à jour avec succès", [], 200);
    }

    public function editPointures($id)
    {
        $data = DB::table('pointures')
        ->where('pointures.id_pointures', $id)
            ->get();

        return $this->apiResponse(200, "Pointure récupérée avec succès",  $data, 200);
    }

    public function SavePointures(Request $request)
    {
        $today = Carbon::today();

        DB::table('pointures')->insertGetId([
            'name_pointures' => $request->libelle,
            'created_at' => $today,
        ]);

        return $this->apiResponse(200, "Pointure créée avec succès", [], 200);
    }

    public function updatePointures(Request $request)
    {
        DB::table('pointures')
        ->where('id_pointures', '=', $request->id_pointure)
            ->update(['name_pointures' => $request->libelle]);

        return $this->apiResponse(200, "Pointure mise à jour avec succès", [], 200);
    }


    public function getAllsouscategories($id)
    {
        $datas = DB::table('sub_categories')
        ->join('categories', 'categories.id_categories', '=', 'sub_categories.categories_id_sub')
        ->orderBy('sub_categories.id_sub_categories', 'desc')
        ->get();

        $categories = DB::table('categories')
        ->orderBy('categories.id_categories', 'desc')
        ->get();

        $response = [
            'datas' => $datas,
            'categories' => $categories,
        ];

        return $this->apiResponse(200, "Sous-catégories récupérées avec succès", $response, 200);
    }

    public function saveSouscategories(Request $request)
    {
        $dataselected = $request->selected;
        $today = Carbon::today();

        foreach ($dataselected as $data) {
            DB::table('sous_categories_produits')->insert([
                'idcategories_produits' => $data,
                'libelle_sous_categories_produits' => $request->libelle,
                'slug_sous_categories_produits' => $request->libelle,
                'created_at' => $today,
            ]);
        }

        return $this->apiResponse(200, "Sous catégorie ajoutée avec succès", [], 200);
    }

    public function deletesouscategories($id)
    {
        DB::table('sub_categories')->where('id_sub_categories', $id)->delete();

        return $this->apiResponse(200, "Sous catégorie supprimée avec succès", [], 200);
    }

    public function updatesouscategories(Request $request)
    {
        DB::table('sous_categories_produits')
        ->where('id_sous_categories_produits', $request->id_sub_categories)
            ->update([
                'libelle_sous_categories_produits' => $request->libelle,
                'slug_sous_categories_produits' => $request->libelle,
                'idcategories_produits' => $request->selected,
            ]);

        return $this->apiResponse(200, "Sous catégorie modifiée avec succès", [], 200);
    }

    public function addcategories(Request $request)
    {
        $uploadedFiles = $request->pics;
        $today = Carbon::today();
        $categorie = "";

        if ($uploadedFiles) {
            $dir = 'Categories/';
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $str1 = '0123456789';
            $shuffled = str_shuffle($str);
            $shuffled1 = str_shuffle($str1);
            $code = "Ta-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
            $absolutePath = public_path($dir);
            $extension = $uploadedFiles->getClientOriginalExtension();
            $filename = $code . '.' . $extension;
            $uploadedFiles->move($absolutePath, $filename);
            $relativePath = $dir . $filename;
            $photo = $relativePath;

            $categorie = DB::table('categories_produits')->insert([
                'slug_categories_produits' => $request->libelle,
                'libelle_categories_produits' => $request->libelle,
                'logos_categories_produits' => $photo,
                'created_at' => $today,
            ]);
        } else {
            $categorie = DB::table('categories_produits')->insert([
                'slug_categories_produits' => $request->libelle,
                'libelle_categories_produits' => $request->libelle,
                'created_at' => $today,
            ]);
        }

        return $this->apiResponse(200, "Catégorie ajoutée avec succès", [], 200);
    }

    public function updatecategories(Request $request)
    {
        if ($request->pics) {
            $uploadedFiles = $request->pics;
            $dir = 'Categories/';
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $str1 = '0123456789';
            $shuffled = str_shuffle($str);
            $shuffled1 = str_shuffle($str1);
            $code = "Ta-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
            $absolutePath = public_path($dir);
            $extension = $uploadedFiles->getClientOriginalExtension();
            $filename = $code . '.' . $extension;
            $uploadedFiles->move($absolutePath, $filename);
            $relativePath = $dir . $filename;
            $photo = $relativePath;

            DB::table('categories_produits')
            ->where('id_categories_produits', $request->id_categories)
                ->update([
                    'slug_categories_produits' => $request->slug,
                    'libelle_categories_produits' => $request->libelle,
                    'logos_categories_produits' => $photo,
                ]);
        } else {
            DB::table('categories_produits')
            ->where('id_categories_produits', $request->id_categories)
                ->update([
                    'slug_categories_produits' => $request->slug,
                    'libelle_categories_produits' => $request->libelle,
                    'logos_categories_produits' => $request->Urlimages,
                ]);
        }

        return $this->apiResponse(200, "Catégorie modifiée avec succès", [], 200);
    }

    public function deletecategoriesSouscategorie($id)
    {
        DB::table('sous_categories_produits')
        ->where('id_sous_categories_produits', $id)
            ->delete();

        return $this->apiResponse(200, "Sous-catégorie supprimée avec succès", [], 200);
    }

    public function deletecategoriesSoussouscategorie($id)
    {
        DB::table('sous_sous_categories_produits')
        ->where('id_sous_sous_categories_produits', $id)
            ->delete();

        return $this->apiResponse(200, "Sous-sous-catégorie supprimée avec succès", [], 200);
    }

    public function deletecategoriesproduits($id)
    {
        DB::table('products')
        ->where('id_products', $id)
            ->delete();

        return $this->apiResponse(200, "Produit supprimé avec succès", [], 200);
    }

    public function deletecategories($id)
    {
        DB::table('categories')
        ->where('id_categories', $id)
            ->delete();

        return $this->apiResponse(200, "Catégorie supprimée avec succès", [], 200);
    }

    public function editCatégories($id)
    {
        $data = DB::table('categories_produits')->where('id_categories_produits', $id) ->get();

        return $this->apiResponse(200, "Catégorie récupérée avec succès", $data, 200);
    }

    public function getAllmails($id)
    {
        $datas = DB::table('newsletters')
        ->orderBy('newsletters.newsletters_id', 'desc')
        ->get();

        return $this->apiResponse(200, "Mails récupérés avec succès", $datas, 200);
    }

    public function getAllboutiques($id)
    {
        $datas = DB::table('stores')
        ->join('users', 'users.id', '=', 'stores.idUsers_stores')
        ->orderBy('stores.id_stores', 'desc')
        ->paginate(3);

        return $this->apiResponse(200, "Boutiques récupérées avec succès", $datas, 200);
    }

    public function getAllboutiquesByAdd($id)
    {
        $datas = DB::table('stores')
        ->orderBy('stores.id_stores', 'desc')
        ->get();
        return $this->apiResponse(200, "Toutes les boutiques récupérées avec succès", $datas, 200);
    }

    public function changesstatStores($id, $staus)
    {
        DB::table('stores')
        ->where('id_stores', $id)->update(['status_stores' => $staus]);

        DB::table('users')
        ->join('stores', 'stores.idUsers_stores', '=', 'users.id')
        ->where('stores.id_stores', $id)->update(['users.status' => $staus]);
        return $this->apiResponse(200, "Statut des boutiques modifié avec succès", [], 200);
    }

    public function status_products($id, $staus)
    {
        DB::table('products')
        ->where('id_products', $id)->update(['status_products' => $staus]);
        return $this->apiResponse(200, "Statut des produits modifié avec succès", [], 200);
    }

    public function changescategoriesStates($id, $staus)
    {
        DB::table('categories_produits')
        ->where('id_categories_produits', $id)->update(['state_categories_produits' => $staus]);
        return $this->apiResponse(200, "Statut des catégories modifié avec succès", [], 200);
    }

    public function changesSouscategoriesStates($id, $staus)
    {
        DB::table('sous_categories_produits')
        ->where('id_sous_categories_produits', $id)->update(['state_sous_categories_produits' => $staus]);
        return $this->apiResponse(200, "Statut des sous-catégories modifié avec succès", [], 200);
    }

    public function changesSousSouscategoriesState($id, $staus)
    {
        DB::table('sous_sous_categories_produits')
        ->where('id_sous_sous_categories_produits', $id)->update(['state_sous_sous_categories_produits' => $staus]);
        return $this->apiResponse(200, "Statut des sous-sous-catégories modifié avec succès", [], 200);
    }

    public function deleteproducts($id)
    {
        DB::table('products')
        ->where('id_products', $id)->delete();
        return $this->apiResponse(200, "Produit supprimé avec succès", [], 200);
    }

    public function deleteStores($id)
    {
        $donnes = DB::table('stores')
        ->where('id_stores', '=', $id)
            ->delete();
        return $this->apiResponse(200, "Boutique supprimer avec succès", [], 200);
    }


    public function getAllsoussouscategories($data)
    {
        $data = DB::table('sous_sous_categories_produits')
        ->join('sous_categories_produits', 'sous_categories_produits.id_sous_categories_produits', '=', 'sous_sous_categories_produits.idsous_categories_produits')
        ->orderBy('sous_sous_categories_produits.id_sous_sous_categories_produits', 'desc')
        ->distinct()
            ->paginate(6);

        $datatSouscategories = DB::table('sous_categories_produits')
        ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'sous_categories_produits.idcategories_produits')
        ->orderBy('sous_categories_produits.id_sous_categories_produits', 'desc')
        ->get();

        $response = [
            'datas' => $data,
            'souscategories' => $datatSouscategories,
        ];

        return $this->apiResponse(200, "Sous-sous catégories récupérées avec succès", $response, 200);
    }

    public function editsoussousCategories($id, $idsousCat)
    {
        $data = DB::table('sous_sous_categories_produits')
        ->join('sous_categories_produits', 'sous_categories_produits.id_sous_categories_produits', '=', 'sous_sous_categories_produits.idsous_categories_produits')
        ->orderBy('sous_sous_categories_produits.id_sous_sous_categories_produits', 'desc')
        ->where('sous_sous_categories_produits.id_sous_sous_categories_produits', $id)
            ->get();

        $response = [
            'data' => $data,
        ];

        return $this->apiResponse(200, "Sous-sous catégorie modifiée avec succès", $response, 200);
    }

    public function selectedsoussouscategories($id)
    {
        $data = DB::table('sous_sous_categories_produits')
        ->join('sous_categories_produits', 'sous_categories_produits.id_sous_categories_produits', '=', 'sous_sous_categories_produits.idsous_categories_produits')
        ->orderBy('sous_sous_categories_produits.id_sous_sous_categories_produits', 'desc')
        ->where('sous_sous_categories_produits.idsous_categories_produits', $id)
            ->paginate(6);

        $response = [
            'datas' => $data,
        ];

        return $this->apiResponse(200, "Sous-sous catégories sélectionnées récupérées avec succès", $response, 200);
    }

    public function saveSousSouscategories(Request $request)
    {
        $dataselected = $request->selected;
        $today = Carbon::today();

        foreach ($dataselected as $data) {
            DB::table('sous_sous_categories_produits')->insert([
                'idsous_categories_produits' => $data,
                'libelle_sous_sous_categories' => $request->libelle,
                'slug_sous_sous_categories' => $request->libelle,
                'created_at' => $today,
            ]);
        }

        return $this->apiResponse(200, "Sous-sous catégories ajoutées avec succès", null, 200);
    }

    public function updatesoussouscategories(Request $request)
    {
        DB::table('sous_sous_categories_produits')
        ->where('id_sous_sous_categories_produits', '=', $request->id_sub_sub_categories)
            ->update([
                'libelle_sous_sous_categories' => $request->libelle,
                'slug_sous_sous_categories' => $request->libelle,
                'idsous_categories_produits' => $request->selected,
            ]);

        return $this->apiResponse(200, "Sous-sous catégorie modifiée avec succès", null, 200);
    }

    public function deletesoussouscategories($id)
    {
        DB::table('sub_sub_categories')->where('id_sub_sub_categories', $id)->delete();
        return $this->apiResponse(200, "Sous-sous catégorie supprimée avec succès", null, 200);
    }

    public function addProduits(Request $request)
    {
        $line1 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $line2 = '0123456789';
        $composition1 = str_shuffle($line1);
        $composition2 = str_shuffle($line2);
        $code_products = substr($composition1, 0, 5) . "" . substr($composition2, 0, 1);
        $today = date("Y-m-d");
        $dataPhotos = "";

        $slug = Str::slug($request->libelle, '-');
        $uploadedFiles = $request->fileData;
        $dir = 'Produits/';
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
        $dataPhotos = $relativePath;

        $productsId = DB::table('products')->insertGetId([
            'code_products' => $code_products,
            'id_boutique' => $request->idboutique,
            'name_product' => $request->libelle,
            'slug' => $slug,
            'description' =>  $request->description,
            'description_courte' => $request->longdescription,
            'imageUrl' => $relativePath,
            'price' =>  $request->prix,
            'Sale_price' => $request->prixVente,
            'stoks' => $request->quantiteStock,
            'QteStock' => $request->quantiteStock,
            //'etatStock' =>  $request->Instock,
            'Optionsdecommande' =>  $request->Optionsdecommande,
            'Optionsproduits' =>  $request->Optionsproduits,
            'Occasion' =>  $request->Occasion,
            'Parametre' =>  $request->Parametre,
            'userId' =>  $request->usersId,
            'created_at' => $today,
        ]);


        // ensuite nous allons remplir les tables pivo
        $categoryProduct = DB::table('categoriesByproduits')->insertGetId([
            'categoriesId' => $request->selected1,
            'produitsId' => $productsId,
            'sous_categoriesId' => $request->selected2,
            'sous_sous_categoriesId' =>  $request->selected3,
            'produitsCodes' => $code_products,
        ]);

        if ($request->couleur) {

            $tabcouleur = explode(",", $request->couleur);
            for ($i = 0; $i < count($tabcouleur); $i++) {
                $couleursProduit = DB::table('couleurs_produit')->insert([
                    'couleurs_id' => $tabcouleur[$i],
                    'product_id' => $productsId,
                ]);
            };
        }

        if ($request->pointure) {

            $tabpointure = explode(",", $request->pointure);
            for ($i = 0; $i < count($tabpointure); $i++) {
                $pointuresProduit = DB::table('pointures_produit')->insertGetId([
                    'pointures_id' => $tabpointure[$i],
                    'product_id' => $productsId,
                ]);
            };
        }

        if ($request->taille) {
            $tabtaille = explode(",", $request->taille);
            for ($i = 0; $i < count($tabtaille); $i++) {
                $pointuresProduit = DB::table('produit_size')->insertGetId([
                    'size_id' => $tabtaille[$i],
                    'product_id' => $productsId,
                ]);
            };
        }

        if ($productsId) {
            if ($request->file('files')) {
                $relativePaths = "";
                $status = 1;
                $extension = "";
                $Idcatalogue = "";

                foreach ($request->file('files') as $fichiers) :

                    $dir = 'catalogue/';
                    $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                    $str1 = '0123456789';
                    $shuffled = str_shuffle($str);
                    $shuffled1 = str_shuffle($str1);
                    $code = "Tarafe-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
                    $absolutePath = public_path($dir);
                    $extension = $fichiers->getClientOriginalExtension();
                    $filename = $code . '.' . $extension;
                    $fichiers->move($absolutePath, $filename);
                    $relativePaths = $dir . $filename;
                    $photo = $relativePaths;

                    $Idcatalogue = DB::table('catalogue')->insert([
                        'id_produits' => $productsId,
                        'Urlcatalogue' => $relativePaths,
                        'created_at' => $today,
                    ]);
                endforeach;
            } else {
            }

            //Nous allons metre l'image du produits dans le catalorgue
            $catalogueProductImages = DB::table('catalogue')->insert([
                'id_produits' => $productsId,
                'Urlcatalogue' => $dataPhotos,
                'created_at' => $today,
            ]);
        }

        $success = "";
        $response = [];

        if ($Idcatalogue || $productsId) {

            $success = true;
            $response = [
                'success' => $success,
                'message' => "Produits ajouter  avec succès",
            ];
        } else {

            $success = false;
            $response = [
                'success' => $success,
                'message' => "Quelque chose c'est mal passer mercis de rajouter à nouveau votre produits",
            ];
        }

        return $this->apiResponse(200, "Produits ajouter  avec succès", $response, 200);
    }

    public function updateproduits(Request $request)
    {

        $success = "";
        $response = [];
        $today = date("Y-m-d");
        $slug = Str::slug($request->libelle, '-');
        // idProduits
        $products = DB::table('products')
        ->join('categoriesByproduits', 'categoriesByproduits.produitsId', '=', 'products.id_products')
        ->join('categories_produits', 'categories_produits.id_categories_produits', '=', 'categoriesByproduits.categoriesId')
        ->join('stores', 'stores.id_stores', '=', 'products.id_boutique')
        ->where('products.id_products', $request->idProduits)
            ->update(
                [
                    'id_boutique' => $request->idboutique,
                    'name_product' => $request->libelle,
                    // 'slug' => $slug,
                    'description' =>  $request->description,
                    'description_courte' => $request->longdescription,
                    'price' =>  $request->prix,
                    'Sale_price' => $request->prixVente,
                    'stoks' => $request->quantiteStock,
                    'QteStock' => $request->quantiteStock,
                    'Optionsdecommande' =>  $request->Optionsdecommande,
                    'Optionsproduits' =>  $request->Optionsproduits,
                    'Occasion' =>  $request->Occasion,
                    'Parametre' =>  $request->Parametre,
                    'userId' =>  $request->usersId,
                    // 'updated_at' =>$today,
                ]
            );


        // ensuite nous allons Modifier les tables pivo
        $products = DB::table('categoriesByproduits')
        ->where('categoriesByproduits.produitsId', $request->idProduits)
            ->update([
                'categoriesId' => $request->selected1,
                'sous_categoriesId' => $request->selected2,
                'sous_sous_categoriesId' =>  $request->selected3,
            ]);

        if ($request->couleur) {

            // $tabcouleur = explode(", ", $request->couleur);
            // for ($i = 0; $i < count($tabcouleur); $i++) {

            foreach ($request->couleur as $item) :
                $couleursProduit = DB::table('couleurs_produit')
                ->where('couleurs_produit.product_id', $request->idProduits)
                    ->update([
                        'couleurs_id' => $item,
                    ]);
            endforeach;
            // };
        }

        if ($request->pointure) {

            // $tabpointure = explode(",", $request->pointure);
            // for ($i = 0; $i < count($tabpointure); $i++) {
            foreach ($request->pointure as $item) :
                $couleursProduit = DB::table('pointures_produit')
                ->where('pointures_produit.product_id', $request->idProduits)
                    ->update([
                        'pointures_id' => $item,
                    ]);
            endforeach;
            // };
        }

        if ($request->taille) {

            // $tabtaille = explode(",", $request->taille);
            // for ($i = 0; $i < count($tabtaille); $i++) {
            foreach ($request->taille as $item) :
                $couleursProduit = DB::table('produit_size')
                ->where('produit_size.product_id', $request->idProduits)
                    ->update([
                        'size_id' => $item,
                    ]);
            endforeach;
            // };
        }

        $success = true;

        return $this->apiResponse(200, "Produits Modifier avec succès", null, 200);
    }

    // Fonction pour supprimer une image
    public function removeImages($cataloges, $produits)
    {
        $datas = DB::table('catalogue')
        ->where('catalogue.id_catalogue', $cataloges)
            ->where('catalogue.id_produits', $produits)
            ->delete();

        $messages = "Sous-catégorie supprimée avec succès";
        $response = [
            'messages' => $messages,
        ];
        return $this->apiResponse(200, "Suppression effectuée avec succès", $response, 200);
    }

    // Fonction pour vérifier les paramètres d'un produit
    public function checkPrams($prams, $id)
    {
        $parametre = DB::table('parametre_produit')
        ->where('parametre_produit.options_parametre_produit', '=', $prams)
            ->where('parametre_produit.id_produitparametre', '=', $id)
            ->get();

        $response = [
            'data' => $parametre,
        ];
        return $this->apiResponse(200, "Paramètres récupérés avec succès", $response, 200);
    }

    // Fonction pour ajouter un paramètre
    public function addparametre(Request $request)
    {
        $uploadedFiles = $request->file('pics');
        $usersid = $request->usersid;
        $today = Carbon::today();
        $relativePath = "";
        $data = "";
        $messages = "";

        if ($request->selectedParams == 1 || $request->selectedParams == 2 || $request->selectedParams == 3 || $request->selectedParams == 5 || $request->selectedParams == 6 || $request->selectedParams == 7) {
            $idparametre = DB::table('parametre_produit')->insertGetId([
                'id_produitparametre' => $request->id_produits,
                'dimensions_parametre' => $request->dimensions,
                'texte_parametre' => $request->texteProduits,
                'logos_parametre' => $request->logoParams,
                'options_parametre_produit' => $request->selectedParams,
                'couleur_parametre_produit' => $request->couleur,
                'tailles_parametre_produit' => $request->tailles,
                'pointures_parametre_produit' => $request->pointures,
                'prix_parametre_produit' => $request->prix,
                'usersid_parametre' => $request->usersid,
                'created_at' => $today,
            ]);

            $messages = "Paramètre ajouté avec succès";
        }

        if ($request->selectedParams == 4) {
            foreach ($request->file('files') as $file) {
                $dir = 'cataloguePagne/';
                $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $str1 = '0123456789';
                $shuffled = str_shuffle($str);
                $shuffled1 = str_shuffle($str1);
                $code = "pagnes-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
                $absolutePath = public_path($dir);
                $extension = $file->getClientOriginalExtension();
                $filename = $code . '.' . $extension;
                $file->move($absolutePath, $filename);
                $relativePath = $dir . $filename;
                $photo = $relativePath;

                // Ajout notre gallerie
                DB::table('pagnes')->insertGetId([
                    'options_parametrepagne' => $request->selectedParams,
                    'Id_produitpagnes' => $request->id_produits,
                    'prix_pagnes' => $request->prix,
                    'Path_pagnes' => $relativePath,
                    'usersid_pagne' => $request->usersid,
                    'created_at' => $today,
                ]);
            }

            $messages = "Pagne ajouté avec succès";
        }

        $response = [
            'data' => $messages,
        ];
        return $this->apiResponse(200, "Paramètre ajouté avec succès", $response, 200);
    }

    // Fonction pour uploader des fichiers
    public function fileupload(Request $request)
    {
        $uploadedFiles = $request->id_produits;
        $id_produits = $request->id_produits;
        $today = Carbon::today();
        $relativePath = "";

        foreach ($request->file('pics') as $file) {
            $dir = 'catalogue/';
            $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $str1 = '0123456789';
            $shuffled = str_shuffle($str);
            $shuffled1 = str_shuffle($str1);
            $code = "Ta-" . substr($shuffled1, 0, 5) . "" . substr($shuffled, 0, 1);
            $absolutePath = public_path($dir);
            $extension = $file->getClientOriginalExtension();
            $filename = $code . '.' . $extension;
            $file->move($absolutePath, $filename);
            $relativePath = $dir . $filename;

            //Ajout notre gallerie
            DB::table('catalogue')->insert([
                'id_produits' => $id_produits,
                'Urlcatalogue' => $relativePath,
                'created_at' => $today,
            ]);
        }

        $response = [
            'data' => "Fichiers téléchargés avec succès",
        ];
        return $this->apiResponse(200, "Téléchargement effectué avec succès", $response, 200);
    }

    // Fonction pour récupérer le catalogue d'un produit
    public function getcatalogue($id)
    {
        $datas = DB::table('catalogue')
        ->where('id_produits', $id)
            ->orderBy('catalogue.id_catalogue', 'desc')
            ->get();

        $response = [
            'data' => $datas,
        ];
        return $this->apiResponse(200, "Catalogue récupéré avec succès", $response, 200);
    }

    // Fonction pour rechercher des produits par date
    public function searchProductbyDate($date1, $date2)
    {
        $products = DB::table('products')
        ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
        ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
        ->whereBetween('products.created_at', [$date1, $date2])->get();

        $response = [
            'data' => $products,
        ];
        return $this->apiResponse(200, "Produits trouvés avec succès", $response, 200);
    }

    // Fonction pour rechercher des commandes par date
    public function searchOdersByDate($date1, $date2)
    {
        $products = DB::table('products')
        ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
        ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
        ->join('order_product', 'order_product.product_id', '=', 'products.id_products')
        ->join('orders', 'orders.id_orders', '=', 'order_product.product_id')
        ->whereBetween('orders.created_at', [$date1, $date2])->get();

        $response = [
            'data' => $products,
        ];
        return $this->apiResponse(200, "Commandes trouvées avec succès", $response, 200);
    }

    // Fonction pour rechercher les commandes entre deux dates
    public function searchByOrdersDate($date1, $date2)
    {
        $ordersData = DB::table('orders')
        ->whereBetween('orders.date_orders', [$date1, $date2])
            ->orderBy('orders.date_orders', 'ASC')
            ->distinct()
            ->get();

        $outp = "";

        foreach ($ordersData as  $value) {
            if ($outp != "") {
                $outp .= ",";
            }

            $outp .= '{"id_orders":"' . $value->id_orders . '",';
            $outp .= '"date_orders":"' . $value->date_orders . '",';
            $outp .= '"transaction_id":"' . $value->transaction_id . '",';
            $outp .= '"total":"' . $value->total . '",';
            $outp .= '"heurs_orders":"' . $value->heurs_orders . '",';
            $outp .= '"status_orders":"' . $value->status_orders . '",';
            $outp .= '"created_at":"' . $value->created_at . '",';

            $ordersDataProduict = DB::table('orders')
            ->join('order_product', 'order_product.order_id', '=', 'orders.id_orders')
            ->join('products', 'products.id_products', '=', 'order_product.product_id')
            ->where('orders.id_orders', $value->id_orders)
                ->orderBy('orders.date_orders', 'ASC')
                ->distinct()
                ->get();

            $nb = count($ordersDataProduict);

            $outp1 = "";
            $shuffled = "";
            $shuffled1 = "";
            $str = '#';
            $str1 = '268bd2';

            foreach ($ordersDataProduict as  $values) {
                $shuffled = str_shuffle($str);
                $shuffled1 = str_shuffle($str1);

                $code = substr($shuffled, 0, 1) . "" . substr($shuffled1, 0, 6);

                if ($outp1 != "") {
                    $outp1 .= ",";
                }
                $outp1 .= '{"id_orders":"' . $values->id_orders . '",';
                $outp1 .= '"id_boutique":"' . $values->id_boutique . '",';
                $outp1 .= '"nombres":"' . $nb . '",';
                $outp1 .= '"id_products":"' . $values->id_products . '",';
                $outp1 .= '"name_product":"' . $values->name_product . '",';
                $outp1 .= '"nombres":"' . $nb . '",';
                $outp1 .= '"code":"' . $code . '",';
                $outp1 .= '"code_products":"' . $values->code_products . '"}';
            }

            $outp1 = "[" . $outp1 . "]";
            $outp .= '"produitsOrders":' . $outp1 . '}';
        }

        return $this->apiResponse(200, "Commandes entre les dates spécifiées", json_decode("[" . $outp . "]"), 200);
    }


    // Fonction pour obtenir toutes les dates de commande distinctes
    public function getAllOrdersDate()
    {
        $ordersDate = DB::table('orders')
        ->distinct('date_orders')
        ->orderBy('date_orders', 'asc')
        ->get('date_orders');
        return $this->apiResponse(200, "Dates des commandes récupérées avec succès", $ordersDate, 200);
    }

    // Fonction pour rechercher des produits par magasin
    public function searchProductByStores($data)
    {
        $tabdata = explode(",", $data);
        $products = DB::table('products')
        ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
        ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
        ->whereIn('products.id_boutique', $tabdata)->get();
        return $this->apiResponse(200, "Produits par magasin récupérés avec succès", $products, 200);
    }

    // Fonction pour obtenir les paramètres du produit
    public function getProduitsetting($data)
    {
        $products = DB::table('products')
        ->join('category_product', 'category_product.product_id', '=', 'products.id_products')
        ->join('categories', 'categories.id_categories', '=', 'category_product.category_id')
        ->join('sub_categories', 'sub_categories.categories_id_sub', '=', 'category_product.sub_categories_id')
        ->join('sub_sub_categories', 'sub_sub_categories.categories_id_sub_sub', '=', 'sub_categories.categories_id_sub')
        ->where('products.id_products', '=', $data)->get();
        return $this->apiResponse(200, "Paramètres du produit récupérés avec succès", $products, 200);
    }





}
