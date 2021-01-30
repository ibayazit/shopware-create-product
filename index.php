<?php

require 'main.php';

$r = new Request;

$errorBag = [];
$success = false;

if($_POST){
    $newProduct = [
        'active' => true,
        'name' => $_POST['name'],
        'productNumber' => $_POST['productNumber'],
        'stock' => intval($_POST['stock']),
        'description' => $_POST['description'],
        'price' => [
            [
                'currencyId' => $_POST['currencyId'],
                'gross' => doubleval($_POST['gross']),
                'net' => doubleval($_POST['net']),
                'linked' => false,
            ],
        ],
    ];

    if(isset($_POST['taxId']) && !empty($_POST['taxId'])){
        $newProduct['taxId'] = $_POST['taxId'];
    }
    else{
        $newProduct['tax'] = [
            'name' => $_POST['tax_name'],
            'taxRate' => doubleval($_POST['tax_rate']),
        ];
    }

    if(isset($_POST['mediaUrl']) && !empty($_POST['mediaUrl'])){

        try {
            $media = $r->post('v3/media?_response=true', []);
            $mediaId = json_decode($media->getBody(), true)['data']['id'];
            
            $mediaFileName = time();
            
            $mediaUpload = $r->post('v3/_action/media/' . $mediaId . '/upload?extension=jpg&fileName=' . $mediaFileName . '&_response=true', [
                'url' => $_POST['mediaUrl']
            ]);

            $newProduct['coverId'] = $mediaId;
            $newProduct['media'][] = [
                'mediaId' => $mediaId,
                'position' => 1
            ];
        } catch (\Throwable $th) {
            header('Content-Type: application/json');
            echo $th->getResponse()->getBody()->getContents();
            exit;
        }
    }

    try {
        $saveNewProduct = $r->post('v3/product', $newProduct);

        $success = true;

    } catch (\Throwable $th) {
        header('Content-Type: application/json');
        echo $th->getResponse()->getBody()->getContents();
        exit;

        $errorBag = array_map(function($item) {
            return [
                'title' => $item['title'] ?? '-',
                'body' => $item['detail'],
            ];
        }, json_decode($th->getResponse()->getBody()->getContents(), true)['errors']);
    }
}

try {
    $tax_response = $r->get('v3/tax');
    $currency_response = $r->get('v3/currency');
    
    
    // header('Content-Type: application/json');

    // $text = $r->get('v3/_info/open-api-schema.json');
    // echo $text->getBody();
    // exit;

    $taxes = array_map(function($item){
        return [
            'id' => $item['id'],
            'title' => $item['attributes']['name'],
        ];
    }, json_decode($tax_response->getBody(), true)['data']);

    $currencies = array_map(function($item){
        return [
            'id' => $item['id'],
            'title' => $item['attributes']['shortName'],
        ];
    }, json_decode($currency_response->getBody(), true)['data']);

} catch (\Throwable $th) {
    die($th->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İbrahim Bayazit</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <form action="index.php" method="post">
            <div class="row">
                <div class="col-md-6 offset-md-3 py-5">
                    <div class="card">
                        <div class="card-body">
                            <?php if(count($errorBag)):?>
                                <div class="alert alert-danger">
                                    <ul>
                                        <?php foreach($errorBag as $error):?>
                                            <li><?=$error['title'] . ' | ' . $error['body']?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif;?>

                            <?php if($success):?>
                                <div class="alert alert-success" role="alert">
                                    Ürün Kaydedildi.
                                </div>
                            <?php endif;?>

                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Ürün Adı</label>
                                        <input type="text" class="form-control" id="name" name="name">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="productNumber" class="form-label">Ürün No</label>
                                        <input type="text" class="form-control" id="productNumber" name="productNumber">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">Stok</label>
                                        <input type="number" class="form-control" id="stock" name="stock" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="currencySelect" class="form-label">Para Birimi</label>
                                    <select class="form-select" id="currencySelect" name="currencyId">
                                        <option value="" selected>Seçiniz</option>
                                        <?php foreach($currencies as $currency):?>
                                        <option value="<?=$currency['id']?>"><?=$currency['title']?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="gross" class="form-label">Değerler</label>
                                    <div class="input-group">
                                        <input type="number" placeholder="Bürüt" step="0.01" class="form-control" id="gross" name="gross">
                                        <input type="number" placeholder="Net" step="0.01" class="form-control" id="net" name="net">
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="taxId" class="form-label">Vergi (Seç)</label>
                                    <select class="form-select" id="taxId" name="taxId">
                                        <option value="" selected>Seçiniz</option>
                                        <?php foreach($taxes as $tax):?>
                                        <option value="<?=$tax['id']?>"><?=$tax['title']?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="taxCreateName" class="form-label">Vergi (Ekle)</label>
                                    <div class="input-group">
                                        <input type="text" placeholder="İsim" id="taxCreateName" class="form-control" name="tax_name">
                                        <input type="number" step="0.01" placeholder="Oran" id="taxCreatePercentage" class="form-control" name="tax_rate">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama</label>
                                <textarea class="form-control" id="description" rows="3" name="description"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="mediaUrl" class="form-label">Kapak fotoğrafı (URL)</label>
                                        <input type="text" class="form-control" id="mediaUrl" name="mediaUrl">
                                    </div>
                                </div>
                            </div>
                            <div class="w-100 text-end">
                                <button type="submit" class="btn btn-sm btn-success">Yeni Ürün Ekle</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>

</html>