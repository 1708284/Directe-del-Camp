<?php

declare(strict_types=1);

namespace App\Controlador;

use App\Modelo\Product;
use Throwable;

final class CatalogController extends BaseController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    public function home()
    {
        $featuredProducts = [];
        $farms            = [];
        $categories       = [];
        $comarques        = [];

        try {
            $featuredProducts = $this->productModel->getFeaturedProducts();
            $farms            = $this->productModel->getActiveFarms();
            $categories       = $this->productModel->getAllCategories();
            $comarques        = $this->productModel->getActiveComarques();
        } catch (Throwable $e) {
            error_log('Catalog home error: ' . $e->getMessage());
        }

        $viewData = [];
        $viewData['pageTitle']        = 'Inici';
        $viewData['featuredProducts'] = $featuredProducts;
        $viewData['farms']            = $farms;
        $viewData['categories']       = $categories;
        $viewData['comarques']        = $comarques;

        $this->render('cataleg/home', $viewData);
    }

    public function index()
    {
        $filters = [];

        $categoria = '';
        if (isset($_GET['categoria'])) {
            $categoria = trim((string) $_GET['categoria']);
        }
        $filters['categoria'] = $categoria;

        $racesSelected = [];
        if (isset($_GET['races']) && is_array($_GET['races'])) {
            foreach ($_GET['races'] as $rId) {
                $intId = (int) $rId;
                if ($intId > 0) {
                    $racesSelected[] = $intId;
                }
            }
        }
        $filters['races'] = $racesSelected;

        $comarca = '';
        if (isset($_GET['comarca'])) {
            $comarca = trim((string) $_GET['comarca']);
        }
        $filters['comarca'] = $comarca;

        $idRamader = '';
        if (isset($_GET['id_ramader'])) {
            $idRamader = trim((string) $_GET['id_ramader']);
        }
        $filters['id_ramader'] = $idRamader;

        $preuMax = '';
        if (isset($_GET['preu_max']) && is_numeric($_GET['preu_max'])) {
            $preuMax = (string) $_GET['preu_max'];
        }
        $filters['preu_max'] = $preuMax;

        $ecologic = false;
        if (isset($_GET['ecologic']) && (string) $_GET['ecologic'] === '1') {
            $ecologic = true;
        }
        $filters['ecologic'] = $ecologic;

        $pastura = false;
        if (isset($_GET['pastura']) && (string) $_GET['pastura'] === '1') {
            $pastura = true;
        }
        $filters['pastura'] = $pastura;

        $q = '';
        if (isset($_GET['q'])) {
            $q = trim((string) $_GET['q']);
        }
        $filters['q'] = $q;

        $ordre = 'recents';
        if (isset($_GET['ordre'])) {
            $reqOrdre = (string) $_GET['ordre'];
            if ($reqOrdre === 'preu_asc' || $reqOrdre === 'preu_desc' || $reqOrdre === 'recents') {
                $ordre = $reqOrdre;
            }
        }
        $filters['ordre'] = $ordre;

        $products   = [];
        $categories = [];
        $allRaces   = [];
        $comarques  = [];

        try {
            $products   = $this->productModel->getPublicCatalog($filters);
            $categories = $this->productModel->getAllCategories();
            $allRaces   = $this->productModel->getAllRaces();
            $comarques  = $this->productModel->getActiveComarques();
        } catch (Throwable $e) {
            error_log('Catalog index error: ' . $e->getMessage());
        }

        $activeCategoryId = 0;
        if ($categoria !== '') {
            if (ctype_digit($categoria)) {
                $activeCategoryId = (int) $categoria;
            } else {
                foreach ($categories as $catItem) {
                    if (mb_strtolower((string) $catItem['nom']) === mb_strtolower($categoria)) {
                        $activeCategoryId = (int) $catItem['id'];
                        break;
                    }
                }
            }
        }

        $visibleRaces = [];
        foreach ($allRaces as $rItem) {
            if ($activeCategoryId === 0 || (int) $rItem['id_categoria_animal'] === $activeCategoryId) {
                $visibleRaces[] = $rItem;
            }
        }

        $viewData = [];
        $viewData['pageTitle']        = 'Catàleg de productes';
        $viewData['products']         = $products;
        $viewData['categories']       = $categories;
        $viewData['races']            = $visibleRaces;
        $viewData['comarques']        = $comarques;
        $viewData['filters']          = $filters;
        $viewData['activeCategoryId'] = $activeCategoryId;

        $this->render('cataleg/index', $viewData);
    }

    public function show()
    {
        $id = '';
        if (isset($_GET['id'])) {
            $id = trim((string) $_GET['id']);
        }

        if ($id === '') {
            http_response_code(404);
            $viewData = [];
            $viewData['pageTitle'] = 'Producte no trobat';
            $viewData['product']   = null;
            $this->render('cataleg/show', $viewData);
            return;
        }

        $product = null;
        try {
            $product = $this->productModel->getPublicProductById($id);
        } catch (Throwable $e) {
            error_log('Catalog show error: ' . $e->getMessage());
        }

        if ($product === null) {
            http_response_code(404);
        }

        $pageTitle = 'Producte no trobat';
        if ($product !== null && isset($product['nom'])) {
            $pageTitle = (string) $product['nom'];
        }

        $viewData = [];
        $viewData['pageTitle'] = $pageTitle;
        $viewData['product']   = $product;

        $this->render('cataleg/show', $viewData);
    }

    public function farms()
    {
        $farms     = [];
        $comarques = [];

        try {
            $farms     = $this->productModel->getActiveFarms();
            $comarques = $this->productModel->getActiveComarques();
        } catch (Throwable $e) {
            error_log('Catalog farms error: ' . $e->getMessage());
        }

        $viewData = [];
        $viewData['pageTitle'] = 'Les nostres granges';
        $viewData['farms']     = $farms;
        $viewData['comarques'] = $comarques;

        $this->render('cataleg/granges', $viewData);
    }
}
