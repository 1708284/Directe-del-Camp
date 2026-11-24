<?php

declare(strict_types=1);

namespace App\Controlador;

use App\Database;
use App\Modelo\Product;
use Throwable;

final class ProductController extends BaseController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new Product();
    }

    private function requireRancher()
    {
        $userRole = '';
        if (isset($_SESSION['user']['rol'])) {
            $userRole = (string) $_SESSION['user']['rol'];
        }

        if (empty($_SESSION['user']) || $userRole !== 'RAMADER') {
            http_response_code(403);
            exit('Accés denegat');
        }
    }

    private function requireOwnership(string $id)
    {
        $product = $this->productModel->findById($id);
        if (!$product || $product['id_ramader'] !== $_SESSION['user']['id']) {
            http_response_code(403);
            exit('Accés denegat');
        }
        return $product;
    }

    private function uploadImageToSupabase(array $file)
    {
        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $allowedMimes = [];
        $allowedMimes['image/jpeg'] = 'jpg';
        $allowedMimes['image/png']  = 'png';
        $allowedMimes['image/webp'] = 'webp';
        $allowedMimes['image/gif']  = 'gif';

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!isset($allowedMimes[$mime])) {
            return null;
        }

        $ext = $allowedMimes[$mime];
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;

        $supabaseUrl = Database::env('SUPABASE_URL', '');
        $serviceKey  = Database::env('SUPABASE_SERVICE_KEY', '');

        if ($supabaseUrl === '' || $serviceKey === '') {
            error_log('Supabase storage credentials missing');
            return null;
        }

        $bucket = 'productes';
        $uploadUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/' . $bucket . '/' . $filename;

        $fileContent = file_get_contents($file['tmp_name']);

        $headers = [];
        $headers[] = 'Authorization: Bearer ' . $serviceKey;
        $headers[] = 'apiKey: ' . $serviceKey;
        $headers[] = 'Content-Type: ' . $mime;

        $httpOptions = [];
        $httpOptions['method']        = 'POST';
        $httpOptions['header']        = implode("\r\n", $headers);
        $httpOptions['content']       = $fileContent;
        $httpOptions['timeout']       = 30;
        $httpOptions['ignore_errors'] = true;

        $contextOptions = [];
        $contextOptions['http'] = $httpOptions;

        $ctx = stream_context_create($contextOptions);
        $res = @file_get_contents($uploadUrl, false, $ctx);

        if ($res === false) {
            error_log('Failed to upload image to Supabase');
            return null;
        }

        $publicUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/public/' . $bucket . '/' . $filename;
        return $publicUrl;
    }

    public function index()
    {
        $this->requireRancher();
        $products = $this->productModel->findByRancher($_SESSION['user']['id']);

        $kpiTotal      = count($products);
        $kpiActive     = 0;
        $kpiOutOfStock = 0;

        foreach ($products as $prod) {
            if (!empty($prod['actiu'])) {
                $kpiActive = $kpiActive + 1;
            }
            if ((int) $prod['estoc_unitats'] <= 0) {
                $kpiOutOfStock = $kpiOutOfStock + 1;
            }
        }

        $viewData = [];
        $viewData['pageTitle']     = 'Els meus productes';
        $viewData['sidebarActive'] = 'productes';
        $viewData['products']      = $products;
        $viewData['kpiTotal']      = $kpiTotal;
        $viewData['kpiActive']     = $kpiActive;
        $viewData['kpiOutOfStock'] = $kpiOutOfStock;
        $this->render('productes/index', $viewData);
    }

    public function create()
    {
        $this->requireRancher();
        $categories = $this->productModel->getAllCategories();
        $races      = $this->productModel->getAllRaces();

        $old = [];
        if (isset($_SESSION['flash_old'])) {
            $old = $_SESSION['flash_old'];
        }
        unset($_SESSION['flash_old']);

        $viewData = [];
        $viewData['pageTitle']     = 'Crear nou producte';
        $viewData['sidebarActive'] = 'crear_producte';
        $viewData['categories']    = $categories;
        $viewData['races']         = $races;
        $viewData['product']       = null;
        $viewData['isEdit']        = false;
        $viewData['old']           = $old;

        $this->render('productes/form', $viewData);
    }

    public function store()
    {
        $this->requireRancher();
        $this->verifyCsrf();

        $nom = '';
        if (isset($_POST['nom'])) {
            $nom = trim((string) $_POST['nom']);
        }

        $descripcio = '';
        if (isset($_POST['descripcio'])) {
            $descripcio = trim((string) $_POST['descripcio']);
        }

        $idRaca = 0;
        if (isset($_POST['id_raca'])) {
            $idRaca = (int) $_POST['id_raca'];
        }

        $format = '';
        if (isset($_POST['format_envasat'])) {
            $format = trim((string) $_POST['format_envasat']);
        }

        $pesNet = 0;
        if (isset($_POST['pes_net_grams'])) {
            $pesNet = (int) $_POST['pes_net_grams'];
        }

        $rawPreu = 0;
        if (isset($_POST['preu_pack'])) {
            $rawPreu = $_POST['preu_pack'];
        }
        $preuPack = filter_var($rawPreu, FILTER_VALIDATE_FLOAT);

        $estoc = -1;
        if (isset($_POST['estoc_unitats']) && $_POST['estoc_unitats'] !== '') {
            $estoc = (int) $_POST['estoc_unitats'];
        }

        $codiLot = '';
        if (isset($_POST['codi_lot_tracabilitat'])) {
            $codiLot = trim((string) $_POST['codi_lot_tracabilitat']);
        }

        $ecologic = isset($_POST['certificat_ecologic']);
        $pastura  = isset($_POST['pastura_lliure']);

        $error = null;
        if ($nom === '' || mb_strlen($nom) > 150) {
            $error = 'El nom del producte és obligatori (màx. 150 caràcters).';
        } elseif ($idRaca <= 0) {
            $error = 'Has de seleccionar una raça vàlida.';
        } elseif ($format === '' || mb_strlen($format) > 100) {
            $error = 'El format d\'envasat és obligatori (màx. 100 caràcters).';
        } elseif ($pesNet <= 0) {
            $error = 'El pes net ha de ser superior a 0 grams.';
        } elseif ($preuPack === false || $preuPack <= 0) {
            $error = 'El preu ha de ser un número superior a 0.';
        } elseif ($estoc < 0) {
            $error = 'L\'estoc ha de ser un número igual o superior a 0.';
        } elseif ($codiLot === '' || mb_strlen($codiLot) > 50) {
            $error = 'El codi de lot de traçabilitat és obligatori (màx. 50 caràcters).';
        }

        if ($error !== null) {
            $_SESSION['flash_error'] = $error;
            $_SESSION['flash_old']   = $_POST;
            $this->redirect('/ramader/productes/crear');
        }

        $imageUrl = null;
        if (isset($_FILES['imatge']) && $_FILES['imatge']['error'] === UPLOAD_ERR_OK) {
            $imageUrl = $this->uploadImageToSupabase($_FILES['imatge']);
        }

        try {
            $productData = [];
            $productData['id_ramader']            = $_SESSION['user']['id'];
            $productData['id_raca']               = $idRaca;
            $productData['nom']                   = $nom;
            $productData['descripcio']            = $descripcio;
            $productData['format_envasat']        = $format;
            $productData['pes_net_grams']         = $pesNet;
            $productData['preu_pack']             = number_format((float) $preuPack, 2, '.', '');
            $productData['estoc_unitats']         = $estoc;
            $productData['codi_lot_tracabilitat'] = $codiLot;
            $productData['certificat_ecologic']   = $ecologic;
            $productData['pastura_lliure']        = $pastura;
            $productData['actiu']                 = true;
            $productData['imatge_url']            = $imageUrl;

            $this->productModel->create($productData);
            $_SESSION['flash_success'] = 'Producte creat correctament.';
        } catch (Throwable $e) {
            error_log('Store product error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'No s\'ha pogut desar el producte.';
            $_SESSION['flash_old']   = $_POST;
            $this->redirect('/ramader/productes/crear');
        }

        $this->redirect('/ramader/productes');
    }

    public function edit()
    {
        $this->requireRancher();

        $id = '';
        if (isset($_GET['id'])) {
            $id = (string) $_GET['id'];
        }

        if ($id === '') {
            http_response_code(400);
            exit('ID no proporcionat');
        }

        $product    = $this->requireOwnership($id);
        $categories = $this->productModel->getAllCategories();
        $races      = $this->productModel->getAllRaces();

        $old = [];
        if (isset($_SESSION['flash_old'])) {
            $old = $_SESSION['flash_old'];
        }
        unset($_SESSION['flash_old']);

        $viewData = [];
        $viewData['pageTitle']     = 'Editar producte';
        $viewData['sidebarActive'] = 'productes';
        $viewData['categories']    = $categories;
        $viewData['races']         = $races;
        $viewData['product']       = $product;
        $viewData['isEdit']        = true;
        $viewData['old']           = $old;

        $this->render('productes/form', $viewData);
    }

    public function update()
    {
        $this->requireRancher();
        $this->verifyCsrf();

        $id = '';
        if (isset($_POST['id'])) {
            $id = (string) $_POST['id'];
        }

        if ($id === '') {
            http_response_code(400);
            exit('ID no proporcionat');
        }

        $product = $this->requireOwnership($id);

        $nom = '';
        if (isset($_POST['nom'])) {
            $nom = trim((string) $_POST['nom']);
        }

        $descripcio = '';
        if (isset($_POST['descripcio'])) {
            $descripcio = trim((string) $_POST['descripcio']);
        }

        $idRaca = 0;
        if (isset($_POST['id_raca'])) {
            $idRaca = (int) $_POST['id_raca'];
        }

        $format = '';
        if (isset($_POST['format_envasat'])) {
            $format = trim((string) $_POST['format_envasat']);
        }

        $pesNet = 0;
        if (isset($_POST['pes_net_grams'])) {
            $pesNet = (int) $_POST['pes_net_grams'];
        }

        $rawPreu = 0;
        if (isset($_POST['preu_pack'])) {
            $rawPreu = $_POST['preu_pack'];
        }
        $preuPack = filter_var($rawPreu, FILTER_VALIDATE_FLOAT);

        $estoc = -1;
        if (isset($_POST['estoc_unitats']) && $_POST['estoc_unitats'] !== '') {
            $estoc = (int) $_POST['estoc_unitats'];
        }

        $codiLot = '';
        if (isset($_POST['codi_lot_tracabilitat'])) {
            $codiLot = trim((string) $_POST['codi_lot_tracabilitat']);
        }

        $ecologic = isset($_POST['certificat_ecologic']);
        $pastura  = isset($_POST['pastura_lliure']);

        $error = null;
        if ($nom === '' || mb_strlen($nom) > 150) {
            $error = 'El nom del producte és obligatori (màx. 150 caràcters).';
        } elseif ($idRaca <= 0) {
            $error = 'Has de seleccionar una raça vàlida.';
        } elseif ($format === '' || mb_strlen($format) > 100) {
            $error = 'El format d\'envasat és obligatori (màx. 100 caràcters).';
        } elseif ($pesNet <= 0) {
            $error = 'El pes net ha de ser superior a 0 grams.';
        } elseif ($preuPack === false || $preuPack <= 0) {
            $error = 'El preu ha de ser un número superior a 0.';
        } elseif ($estoc < 0) {
            $error = 'L\'estoc ha de ser un número igual o superior a 0.';
        } elseif ($codiLot === '' || mb_strlen($codiLot) > 50) {
            $error = 'El codi de lot de traçabilitat és obligatori (màx. 50 caràcters).';
        }

        if ($error !== null) {
            $_SESSION['flash_error'] = $error;
            $_SESSION['flash_old']   = $_POST;
            $this->redirect('/ramader/productes/editar?id=' . urlencode($id));
        }

        $finalImageUrl = $product['imatge_url'];
        if (isset($_FILES['imatge']) && $_FILES['imatge']['error'] === UPLOAD_ERR_OK) {
            $newImageUrl = $this->uploadImageToSupabase($_FILES['imatge']);
            if ($newImageUrl !== null) {
                $finalImageUrl = $newImageUrl;
            }
        }

        try {
            $updateData = [];
            $updateData['id_raca']               = $idRaca;
            $updateData['nom']                   = $nom;
            $updateData['descripcio']            = $descripcio;
            $updateData['format_envasat']        = $format;
            $updateData['pes_net_grams']         = $pesNet;
            $updateData['preu_pack']             = number_format((float) $preuPack, 2, '.', '');
            $updateData['estoc_unitats']         = $estoc;
            $updateData['codi_lot_tracabilitat'] = $codiLot;
            $updateData['certificat_ecologic']   = $ecologic;
            $updateData['pastura_lliure']        = $pastura;
            $updateData['imatge_url']            = $finalImageUrl;

            $this->productModel->update($id, $_SESSION['user']['id'], $updateData);
            $_SESSION['flash_success'] = 'Producte actualitzat correctament.';
        } catch (Throwable $e) {
            error_log('Update product error: ' . $e->getMessage());
            $_SESSION['flash_error'] = 'No s\'ha pogut actualitzar el producte.';
            $_SESSION['flash_old']   = $_POST;
            $this->redirect('/ramader/productes/editar?id=' . urlencode($id));
        }

        $this->redirect('/ramader/productes');
    }

    public function toggle()
    {
        $this->requireRancher();
        $this->verifyCsrf();

        $id = '';
        if (isset($_POST['id'])) {
            $id = (string) $_POST['id'];
        }

        if ($id === '') {
            http_response_code(400);
            exit('ID no proporcionat');
        }

        $this->requireOwnership($id);
        $this->productModel->toggleActive($id, $_SESSION['user']['id']);

        $_SESSION['flash_success'] = 'Estat del producte modificat correctament.';
        $this->redirect('/ramader/productes');
    }

    public function delete()
    {
        $this->requireRancher();
        $this->verifyCsrf();

        $id = '';
        if (isset($_POST['id'])) {
            $id = (string) $_POST['id'];
        }

        if ($id === '') {
            http_response_code(400);
            exit('ID no proporcionat');
        }

        $this->requireOwnership($id);
        $this->productModel->delete($id, $_SESSION['user']['id']);

        $_SESSION['flash_success'] = 'Producte eliminat correctament.';
        $this->redirect('/ramader/productes');
    }

    public function storeCategoryAjax()
    {
        $this->requireRancher();
        $this->verifyCsrf();

        $nom = '';
        if (isset($_POST['nom'])) {
            $nom = trim((string) $_POST['nom']);
        }

        if ($nom === '' || mb_strlen($nom) > 100) {
            header('Content-Type: application/json');
            $res = [];
            $res['ok'] = false;
            $res['error'] = 'El nom de l\'espècie és obligatori (màx. 100 caràcters).';
            echo json_encode($res);
            exit;
        }

        try {
            $newId = $this->productModel->createCategory($nom);
            header('Content-Type: application/json');
            $res = [];
            $res['ok']  = true;
            $res['id']  = $newId;
            $res['nom'] = $nom;
            echo json_encode($res);
            exit;
        } catch (Throwable $e) {
            error_log('Error creating category: ' . $e->getMessage());
            header('Content-Type: application/json');
            $res = [];
            $res['ok'] = false;
            $res['error'] = 'No s\'ha pogut desar l\'espècie.';
            echo json_encode($res);
            exit;
        }
    }

    public function storeRaceAjax()
    {
        $this->requireRancher();
        $this->verifyCsrf();

        $nom = '';
        if (isset($_POST['nom'])) {
            $nom = trim((string) $_POST['nom']);
        }

        $idCat = 0;
        if (isset($_POST['id_categoria_animal'])) {
            $idCat = (int) $_POST['id_categoria_animal'];
        }

        if ($nom === '' || mb_strlen($nom) > 100 || $idCat <= 0) {
            header('Content-Type: application/json');
            $res = [];
            $res['ok'] = false;
            $res['error'] = 'Dades de raça o espècie no vàlides.';
            echo json_encode($res);
            exit;
        }

        try {
            $newId = $this->productModel->createRace($nom, $idCat);
            header('Content-Type: application/json');
            $res = [];
            $res['ok']                  = true;
            $res['id']                  = $newId;
            $res['id_categoria_animal'] = $idCat;
            $res['nom']                 = $nom;
            echo json_encode($res);
            exit;
        } catch (Throwable $e) {
            error_log('Error creating race: ' . $e->getMessage());
            header('Content-Type: application/json');
            $res = [];
            $res['ok'] = false;
            $res['error'] = 'No s\'ha pogut desar la raça.';
            echo json_encode($res);
            exit;
        }
    }
}
