<?php
include("db.php");

$search = "";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $sql = "SELECT * FROM products 
            WHERE user_id LIKE '%$search%' 
               OR product_name LIKE '%$search%' 
               OR seller_name LIKE '%$search%' 
               OR description LIKE '%$search%' 
               OR seller_address LIKE '%$search%' 
               OR seller_number LIKE '%$search%'
            ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM products ORDER BY id DESC";
}

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// ✅ Fetch ALL products into array ONCE
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
$total_products = count($products);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>View Products</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
    -webkit-tap-highlight-color: transparent;
}

html { -webkit-text-size-adjust: 100%; }

body {
    background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
    background-attachment: fixed;
    padding: 30px 20px;
    min-height: 100vh;
    overflow-x: hidden;
}

/* ===== Main Container ===== */
.wrapper {
    max-width: 1500px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-radius: 24px;
    padding: 35px;
    box-shadow: 
        0 25px 60px rgba(0, 0, 0, 0.6),
        0 0 0 1px rgba(255, 215, 0, 0.1);
    animation: slideUp 0.6s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(40px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ===== Heading ===== */
h2 {
    text-align: center;
    font-size: 38px;
    font-weight: 800;
    margin-bottom: 8px;
    background: linear-gradient(135deg, #f7971e, #ffd200);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: 1px;
}

.sub-title {
    text-align: center;
    color: #888;
    font-size: 13px;
    letter-spacing: 3px;
    text-transform: uppercase;
    margin-bottom: 30px;
    font-weight: 300;
}

/* ===== Stats Bar ===== */
.stats-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #f7971e, #ffd200);
    padding: 14px 25px;
    border-radius: 14px;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 12px;
    box-shadow: 0 8px 30px rgba(247, 151, 30, 0.35);
}

.stats-bar .count {
    color: #1a1a2e;
    font-weight: 600;
    font-size: 15px;
}

.stats-bar .count i { margin-right: 8px; }

.stats-bar .count span {
    background: #1a1a2e;
    color: #ffd200;
    padding: 2px 16px;
    border-radius: 20px;
    font-weight: 700;
    margin-left: 6px;
}

.stats-bar .actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-add {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    background: #1a1a2e;
    color: #ffd200;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-add:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    color: #fff;
}

/* ===== Search Form ===== */
.search-form {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.search-form input {
    width: 420px;
    max-width: 100%;
    padding: 14px 20px;
    border: 2px solid #e8e8e8;
    border-radius: 12px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #f8f9fa;
    font-family: inherit;
}

.search-form input:focus {
    outline: none;
    border-color: #f7971e;
    box-shadow: 0 0 0 4px rgba(247, 151, 30, 0.12);
    background: #fff;
}

.search-form input::placeholder { color: #aaa; }

.search-form button {
    padding: 14px 30px;
    background: linear-gradient(135deg, #f7971e, #ffd200);
    color: #1a1a2e;
    border: none;
    border-radius: 12px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.3s ease;
    box-shadow: 0 6px 20px rgba(247, 151, 30, 0.3);
}

.search-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(247, 151, 30, 0.4);
}

.search-form button i { margin-right: 8px; }

.btn-clear {
    padding: 14px 22px;
    background: #6c757d;
    color: #fff;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.btn-clear:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

/* ===== Table Wrapper ===== */
.table-wrapper {
    overflow-x: auto;
    border-radius: 16px;
    background: #fff;
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.06);
    -webkit-overflow-scrolling: touch;
}

table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 16px;
    overflow: hidden;
}

/* ===== Table Header ===== */
table thead tr {
    background: linear-gradient(135deg, #1a1a2e, #16213e);
}

table th {
    color: #fff;
    padding: 16px 14px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    white-space: nowrap;
}

table th i {
    margin-right: 8px;
    color: #ffd200;
    font-size: 14px;
}

/* ===== Table Body ===== */
table td {
    padding: 14px;
    border-bottom: 1px solid #f1f1f1;
    font-size: 14px;
    color: #333;
    vertical-align: middle;
}

table tbody tr { transition: all 0.25s ease; }

table tbody tr:hover {
    background: linear-gradient(135deg, #fffaf0, #fff5e6);
}

table tbody tr:last-child td { border-bottom: none; }

/* ========================================
   ✅ 3-IMAGE THUMBNAIL ROW
======================================== */
.thumb-row {
    display: flex;
    gap: 6px;
    align-items: center;
}

.thumb-row img {
    width: 55px;
    height: 55px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #f0f0f0;
    transition: all 0.3s ease;
    cursor: pointer;
    display: block;
    background: #f8f9fa;
    flex-shrink: 0;
}

.thumb-row img:hover {
    transform: scale(1.12) rotate(-2deg);
    border-color: #ffd200;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    z-index: 2;
    position: relative;
}

.thumb-row img.active-thumb {
    border-color: #f7971e;
    box-shadow: 0 0 0 2px rgba(247, 151, 30, 0.35);
}

/* ===== Product Name ===== */
.product-name {
    font-weight: 600;
    color: #1a1a2e;
}

/* ===== Price ===== */
.price {
    color: #00b894;
    font-weight: 700;
    font-size: 15px;
    white-space: nowrap;
}

.price i { margin-right: 2px; }

/* ===== Seller Info ===== */
.seller-cell {
    font-size: 13px;
    color: #555;
}

.seller-cell i {
    margin-right: 5px;
    color: #f7971e;
    width: 16px;
}

/* ===== Action Buttons ===== */
.action-cell {
    display: flex;
    gap: 5px;
}

.edit-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    background: linear-gradient(135deg, #0984e3, #6c5ce7);
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(9, 132, 227, 0.25);
    white-space: nowrap;
}

.edit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(9, 132, 227, 0.35);
}

.delete-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 16px;
    background: linear-gradient(135deg, #e17055, #d63031);
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(214, 48, 49, 0.25);
    white-space: nowrap;
}

.delete-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(214, 48, 49, 0.35);
}

/* ===== No Products ===== */
.no-products {
    text-align: center;
    padding: 50px 20px;
    color: #aaa;
}

.no-products i {
    font-size: 45px;
    color: #ddd;
    margin-bottom: 12px;
    display: block;
}

.no-products a {
    color: #f7971e;
    font-weight: 600;
    text-decoration: none;
}

.no-products a:hover { text-decoration: underline; }

/* ========================================
   BADGE
======================================== */
.badge-id {
    display: inline-block;
    background: #f0f0f0;
    padding: 2px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 13px;
    color: #555;
}

/* ===== Row Striping ===== */
table tbody tr:nth-child(even) { background: #fafafa; }
table tbody tr:nth-child(even):hover {
    background: linear-gradient(135deg, #fffaf0, #fff5e6);
}

/* ========================================
   IMAGE POPUP / LIGHTBOX
======================================== */
.image-popup {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.94);
    z-index: 99999;
    justify-content: center;
    align-items: center;
    padding: 30px;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.image-popup.active {
    display: flex;
    animation: popupFadeIn 0.35s ease;
}

@keyframes popupFadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to   { opacity: 1; transform: scale(1); }
}

.image-popup .popup-content {
    position: relative;
    max-width: 900px;
    width: 100%;
    max-height: 95vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
}

.image-popup .popup-content > img {
    max-width: 100%;
    max-height: 60vh;
    border-radius: 16px;
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.8);
    border: 4px solid rgba(255, 215, 0, 0.3);
    object-fit: contain;
    background: #0f0c29;
    animation: imageZoom 0.4s ease;
}

@keyframes imageZoom {
    from { opacity: 0; transform: scale(0.85); }
    to   { opacity: 1; transform: scale(1); }
}

/* ===== Popup Close ===== */
.image-popup .popup-close {
    position: absolute;
    top: -60px;
    right: 0;
    background: rgba(255, 0, 0, 0.2);
    border: 2px solid rgba(255, 255, 255, 0.2);
    color: #fff;
    font-size: 22px;
    cursor: pointer;
    width: 46px;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    padding: 0;
}

.image-popup .popup-close:hover {
    transform: rotate(90deg);
    background: rgba(255, 0, 0, 0.5);
    border-color: #ff6b6b;
}

/* ===== Popup Nav ===== */
.image-popup .popup-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.1);
    border: 2px solid rgba(255, 255, 255, 0.15);
    color: #fff;
    font-size: 20px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    z-index: 5;
}

.image-popup .popup-nav:hover {
    background: rgba(255, 215, 0, 0.3);
    border-color: #ffd200;
    transform: translateY(-50%) scale(1.1);
}

.image-popup .popup-nav.prev { left: -60px; }
.image-popup .popup-nav.next { right: -60px; }

/* ===== Popup Thumbnails ===== */
.image-popup .popup-thumbs {
    display: flex;
    gap: 10px;
    justify-content: center;
    padding: 8px 14px;
    background: rgba(0, 0, 0, 0.55);
    border-radius: 14px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.image-popup .popup-thumbs .pthumb {
    width: 58px;
    height: 58px;
    border-radius: 10px;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.15);
    cursor: pointer;
    transition: all 0.3s ease;
    background: #0f0c29;
    flex-shrink: 0;
}

.image-popup .popup-thumbs .pthumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.image-popup .popup-thumbs .pthumb:hover,
.image-popup .popup-thumbs .pthumb.active {
    border-color: #ffd200;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(255, 210, 0, 0.4);
}

/* ===== Popup Info Bar ===== */
.image-popup .popup-info {
    color: #fff;
    text-align: center;
    background: rgba(0, 0, 0, 0.6);
    padding: 14px 30px;
    border-radius: 12px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    width: 100%;
    max-width: 600px;
}

.image-popup .popup-info .product-title {
    font-weight: 600;
    color: #ffd200;
    font-size: 18px;
}

.image-popup .popup-info .product-price {
    color: #00b894;
    font-weight: 600;
    font-size: 18px;
}

.image-popup .popup-info .product-seller {
    color: #aaa;
    font-size: 14px;
}

.image-popup .popup-info .product-seller i {
    color: #f7971e;
    margin: 0 5px;
}

/* ===== Popup Counter ===== */
.image-popup .popup-counter {
    position: absolute;
    top: -60px;
    left: 0;
    color: rgba(255, 255, 255, 0.7);
    font-size: 13px;
    font-weight: 400;
    background: rgba(0, 0, 0, 0.5);
    padding: 8px 16px;
    border-radius: 20px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    font-family: monospace;
    letter-spacing: 0.5px;
}

/* ========================================
   RESPONSIVE
======================================== */
@media (max-width: 992px) {
    .image-popup .popup-nav.prev { left: 0; }
    .image-popup .popup-nav.next { right: 0; }
    .image-popup .popup-close { top: -55px; right: 0; }
}

@media (max-width: 768px) {
    body { padding: 15px 10px; }
    .wrapper { padding: 18px; border-radius: 18px; }
    h2 { font-size: 26px; }
    .sub-title { font-size: 11px; letter-spacing: 2px; }

    .search-form { flex-direction: column; }
    .search-form input { width: 100%; }
    .search-form button { width: 100%; }

    .stats-bar { flex-direction: column; text-align: center; }
    .stats-bar .actions { width: 100%; justify-content: center; }
    .btn-add { width: 100%; justify-content: center; }

    table { min-width: 900px; }
    .action-cell { flex-direction: column; }
    .edit-btn, .delete-btn { justify-content: center; width: 100%; }

    .thumb-row img { width: 46px; height: 46px; }

    /* Popup */
    .image-popup { padding: 70px 10px 20px; }
    .image-popup .popup-content { max-width: 100%; }
    .image-popup .popup-content > img { max-height: 45vh; }
    .image-popup .popup-nav { width: 40px; height: 40px; font-size: 16px; }
    .image-popup .popup-nav.prev { left: 6px; }
    .image-popup .popup-nav.next { right: 6px; }
    .image-popup .popup-close { top: -50px; right: 10px; width: 40px; height: 40px; font-size: 18px; }
    .image-popup .popup-counter { top: -50px; left: 10px; font-size: 11px; padding: 6px 12px; }
    .image-popup .popup-thumbs { gap: 6px; padding: 6px 10px; }
    .image-popup .popup-thumbs .pthumb { width: 46px; height: 46px; }
    .image-popup .popup-info { padding: 10px 16px; font-size: 12px; }
    .image-popup .popup-info .product-title { font-size: 14px; }
    .image-popup .popup-info .product-price { font-size: 14px; }
    .image-popup .popup-info .product-seller { font-size: 12px; }
}

@media (max-width: 480px) {
    body { padding: 10px 8px; }
    .wrapper { padding: 14px; border-radius: 14px; }
    h2 { font-size: 22px; }
    .sub-title { font-size: 10px; letter-spacing: 1.5px; margin-bottom: 20px; }

    .stats-bar { padding: 12px 16px; }
    .stats-bar .count { font-size: 13px; }
    .stats-bar .count span { padding: 2px 12px; font-size: 12px; }

    .search-form input { padding: 12px 16px; font-size: 13px; }
    .search-form button { padding: 12px 20px; font-size: 13px; }
    .btn-clear { padding: 12px 18px; font-size: 13px; }

    table { min-width: 800px; }
    table th { padding: 12px 10px; font-size: 11px; }
    table td { padding: 12px 10px; font-size: 13px; }

    .thumb-row { gap: 4px; }
    .thumb-row img { width: 40px; height: 40px; border-radius: 8px; }

    .price { font-size: 13px; }
    .seller-cell { font-size: 12px; }
    .edit-btn, .delete-btn { padding: 6px 12px; font-size: 11px; }

    /* Popup */
    .image-popup .popup-content > img { max-height: 40vh; }
    .image-popup .popup-thumbs .pthumb { width: 42px; height: 42px; }
    .image-popup .popup-info .product-title { font-size: 13px; }
}

/* Custom Scrollbar */
::-webkit-scrollbar { width: 8px; height: 8px; }
::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #f7971e, #ffd200);
    border-radius: 10px;
}
::-webkit-scrollbar-thumb:hover { background: #f7971e; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
</style>

<body>
    <div class="wrapper">
        <h2>🚲 All Products</h2>
        <div class="sub-title">Manage your product inventory</div>
        
        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="count">
                <i class="fas fa-boxes"></i> Total Products: <span><?php echo $total_products; ?></span>
            </div>
            <div class="actions">
                <a href="add_product.php" class="btn-add">
                    <i class="fas fa-plus-circle"></i> Add Product
                </a>
            </div>
        </div>
        
        <!-- Search Form -->
        <form class="search-form" method="GET">
            <input type="text" name="search" placeholder="🔍 Search by product name, seller, address..."
                value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i> Search</button>
            <?php if (!empty($search)): ?>
                <a href="view_product.php" class="btn-clear"><i class="fas fa-times"></i> Clear</a>
            <?php endif; ?>
        </form>

        <!-- Table -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-hashtag"></i> User ID</th>
                        <th><i class="fas fa-hashtag"></i> ID</th>
                        <th><i class="fas fa-images"></i> Images</th>
                        <th><i class="fas fa-box-open"></i> Product Name</th>
                        <th><i class="fas fa-tag"></i> Price</th>
                        <th><i class="fas fa-info-circle"></i> Model</th>
                        <th><i class="fas fa-user"></i> Seller</th>
                        <th><i class="fas fa-phone"></i> Number</th>
                        <th><i class="fas fa-map-marker-alt"></i> Address</th>
                        <th><i class="fas fa-cogs"></i> Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_products > 0): ?>
                        <?php foreach ($products as $row): ?>
                            <?php
                                $img1 = !empty($row['image'])  ? $row['image']  : 'placeholder.jpg';
                                $img2 = !empty($row['image1']) ? $row['image1'] : $img1;
                                $img3 = !empty($row['image2']) ? $row['image2'] : $img1;
                            ?>
                            <tr>
                                <td>#<?php echo str_pad($row['user_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                <td><span class="badge-id">#<?php echo $row['id']; ?></span></td>
                                <td>
                                    <!-- ✅ 3 thumbnails per product -->
                                    <div class="thumb-row">
                                        <img src="uploads/<?php echo htmlspecialchars($img1); ?>"
                                             alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                             class="active-thumb"
                                             onclick="openPopup(<?php echo (int)$row['id']; ?>, 0)"
                                             title="View image 1">
                                        <img src="uploads/<?php echo htmlspecialchars($img2); ?>"
                                             alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                             onclick="openPopup(<?php echo (int)$row['id']; ?>, 1)"
                                             title="View image 2">
                                        <img src="uploads/<?php echo htmlspecialchars($img3); ?>"
                                             alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                             onclick="openPopup(<?php echo (int)$row['id']; ?>, 2)"
                                             title="View image 3">
                                    </div>
                                </td>
                                <td class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td class="price"><i class="fas fa-rupee-sign"></i> <?php echo number_format((float)$row['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['description'] ?? ''); ?></td>
                                <td class="seller-cell"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($row['seller_name'] ?? ''); ?></td>
                                <td class="seller-cell"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($row['seller_number'] ?? ''); ?></td>
                                <td class="seller-cell"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['seller_address'] ?? ''); ?></td>
                                <td>
                                    <div class="action-cell">
                                        <a class="edit-btn" href="edit_product.php?id=<?php echo (int)$row['id']; ?>">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a class="delete-btn" href="delete_product.php?id=<?php echo (int)$row['id']; ?>"
                                            onclick="return confirm('Are you sure you want to delete this product?');">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="no-products">
                                <i class="fas fa-box-open"></i>
                                No products found! <br>
                                <a href="add_product.php">Click here to add your first product</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ========================================
         IMAGE POPUP / LIGHTBOX with 3 images
         ======================================== -->
    <div class="image-popup" id="imagePopup" onclick="closePopup(event)">
        <div class="popup-content" onclick="event.stopPropagation();">

            <button class="popup-close" onclick="closePopup()" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>

            <div class="popup-counter" id="popupCounter">1 / 3</div>

            <button class="popup-nav prev" onclick="changeImage(-1)" aria-label="Previous">
                <i class="fas fa-chevron-left"></i>
            </button>

            <img id="popupImage" src="" alt="Product Image">

            <button class="popup-nav next" onclick="changeImage(1)" aria-label="Next">
                <i class="fas fa-chevron-right"></i>
            </button>

            <!-- ✅ Modal thumbnails -->
            <div class="popup-thumbs" id="popupThumbs">
                <div class="pthumb active" onclick="goToImage(0, event)"><img id="pthumb1" src="" alt="thumb 1"></div>
                <div class="pthumb" onclick="goToImage(1, event)"><img id="pthumb2" src="" alt="thumb 2"></div>
                <div class="pthumb" onclick="goToImage(2, event)"><img id="pthumb3" src="" alt="thumb 3"></div>
            </div>

            <div class="popup-info">
                <span class="product-title" id="popupTitle">Product Name</span> &nbsp;|&nbsp;
                Price: <span class="product-price" id="popupPrice">₹0</span>
                <br>
                <span class="product-seller">
                    <i class="fas fa-user-circle"></i> <span id="popupSeller">Seller</span>
                    <i class="fas fa-phone" style="margin-left:12px;"></i> <span id="popupNumber">-</span>
                </span>
            </div>
        </div>
    </div>

    <script>
        // ✅ Products data from PHP
        const productsData = <?php echo json_encode($products, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        let currentProductIndex = 0;
        let currentImageIndex = 0;
        let currentImages = [];

        function getImages(product) {
            const fallback = product.image || 'placeholder.jpg';
            return [
                product.image  || fallback,
                product.image1 || fallback,
                product.image2 || fallback
            ];
        }

        function openPopup(id, imageIdx) {
            currentProductIndex = productsData.findIndex(p => p.id == id);
            if (currentProductIndex === -1) currentProductIndex = 0;

            const product = productsData[currentProductIndex];
            currentImages = getImages(product);
            currentImageIndex = imageIdx || 0;

            updatePopup();
            document.getElementById('imagePopup').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closePopup(e) {
            if (e && e.target !== e.currentTarget) return;
            document.getElementById('imagePopup').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function changeImage(direction) {
            const nextImg = currentImageIndex + direction;

            if (nextImg >= 0 && nextImg < currentImages.length) {
                currentImageIndex = nextImg;
            } else {
                // Move to next/prev product
                currentProductIndex += direction;
                if (currentProductIndex < 0) currentProductIndex = productsData.length - 1;
                if (currentProductIndex >= productsData.length) currentProductIndex = 0;

                const product = productsData[currentProductIndex];
                currentImages = getImages(product);
                currentImageIndex = direction > 0 ? 0 : currentImages.length - 1;
            }

            updatePopup();
        }

        function goToImage(index, e) {
            if (e) e.stopPropagation();
            if (index >= 0 && index < currentImages.length) {
                currentImageIndex = index;
                updatePopup();
            }
        }

        function updatePopup() {
            const product = productsData[currentProductIndex];
            if (!product) return;

            // Main image
            const popupImg = document.getElementById('popupImage');
            popupImg.src = 'uploads/' + currentImages[currentImageIndex];
            popupImg.alt = product.product_name;

            // Info
            document.getElementById('popupTitle').textContent = product.product_name;
            document.getElementById('popupPrice').textContent = '₹' + parseFloat(product.price).toFixed(2);
            document.getElementById('popupSeller').textContent = product.seller_name || '-';
            document.getElementById('popupNumber').textContent = product.seller_number || '-';

            // Counter — product # and image #
            document.getElementById('popupCounter').textContent =
                (currentProductIndex + 1) + ' / ' + productsData.length +
                '  •  ' + (currentImageIndex + 1) + '/' + currentImages.length;

            // Thumbnails
            for (let i = 0; i < 3; i++) {
                const thumbImg = document.getElementById('pthumb' + (i + 1));
                const thumbWrap = thumbImg.parentElement;
                if (currentImages[i]) {
                    thumbImg.src = 'uploads/' + currentImages[i];
                    thumbWrap.style.display = 'block';
                    thumbWrap.classList.toggle('active', i === currentImageIndex);
                } else {
                    thumbWrap.style.display = 'none';
                }
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (document.getElementById('imagePopup').classList.contains('active')) {
                if (e.key === 'Escape') { closePopup(); e.preventDefault(); }
                if (e.key === 'ArrowLeft') { changeImage(-1); e.preventDefault(); }
                if (e.key === 'ArrowRight') { changeImage(1); e.preventDefault(); }
            }
        });
    </script>
</body>
</html>