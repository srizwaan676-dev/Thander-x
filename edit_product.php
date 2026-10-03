<?php
include("db.php");

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: user/user_product_edit.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("Product not found!");
}

if (isset($_POST['update'])) {

    $product_name   = mysqli_real_escape_string($conn, $_POST['product_name']);
    $price          = mysqli_real_escape_string($conn, $_POST['price']);
    $description    = mysqli_real_escape_string($conn, $_POST['description']);
    $seller_name    = mysqli_real_escape_string($conn, $_POST['seller_name']);
    $seller_number  = mysqli_real_escape_string($conn, $_POST['seller_number']);
    $seller_address = mysqli_real_escape_string($conn, $_POST['seller_address']);

    // ✅ Start with existing images
    $image  = $row['image'];
    $image1 = $row['image1'];
    $image2 = $row['image2'];

    // ✅ Upload helper
    function uploadImage($fileKey, $fallback) {
        if (!empty($_FILES[$fileKey]['name'])) {
            $filename = time() . '_' . basename($_FILES[$fileKey]['name']);
            $tmp      = $_FILES[$fileKey]['tmp_name'];

            // Basic image validation
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $ext     = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                if (move_uploaded_file($tmp, "uploads/" . $filename)) {
                    return $filename;
                }
            }
        }
        return $fallback;
    }

    // ✅ Handle all 3 image uploads
    $image  = uploadImage('image',  $image);
    $image1 = uploadImage('image1', $image1);
    $image2 = uploadImage('image2', $image2);

    $sql = "UPDATE products SET
            product_name   = '$product_name',
            price          = '$price',
            description    = '$description',
            seller_name    = '$seller_name',
            seller_number  = '$seller_number',
            seller_address = '$seller_address',
            image          = '$image',
            image1         = '$image1',
            image2         = '$image2'
            WHERE id='$id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: user/user_product_edit.php");
        exit();
    } else {
        echo mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
        -webkit-tap-highlight-color: transparent;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 30px 15px;
        background:
            radial-gradient(circle at top left, #3b82f655, transparent 35%),
            radial-gradient(circle at bottom right, #22c55e55, transparent 35%),
            linear-gradient(135deg, #020617, #0f172a, #111827);
        background-attachment: fixed;
    }

    /* Form Box */
    .form {
        width: 100%;
        max-width: 520px;
        background: rgba(255, 255, 255, .08);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, .15);
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, .35);
        animation: fade .6s ease;
    }

    @keyframes fade {
        from { opacity: 0; transform: translateY(30px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Heading */
    .form h2 {
        color: #fff;
        text-align: center;
        font-size: 30px;
        margin-bottom: 28px;
        font-weight: 700;
        letter-spacing: .5px;
    }

    .form h2::after {
        content: "";
        display: block;
        width: 90px;
        height: 4px;
        margin: 12px auto 0;
        border-radius: 20px;
        background: linear-gradient(90deg, #2563eb, #22c55e);
    }

    /* Section labels */
    .section-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #cbd5e1;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
        margin: 20px 0 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgba(255, 255, 255, .1);
    }

    .section-label i {
        color: #22c55e;
        font-size: 14px;
    }

    /* Inputs */
    .form input[type="text"],
    .form input[type="number"] {
        width: 100%;
        height: 52px;
        margin-bottom: 14px;
        padding: 0 18px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, .15);
        background: rgba(255, 255, 255, .08);
        color: #fff;
        font-size: 14px;
        outline: none;
        transition: .3s;
    }

    .form input::placeholder { color: #94a3b8; }

    .form input:focus {
        border-color: #3b82f6;
        background: rgba(255, 255, 255, .12);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, .18);
    }

    /* ========================================
       3 IMAGE UPLOAD GRID
    ======================================== */
    .image-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }

    .image-box {
        position: relative;
        border-radius: 14px;
        overflow: hidden;
        border: 2px dashed rgba(255, 255, 255, .2);
        background: rgba(255, 255, 255, .04);
        aspect-ratio: 1 / 1;
        transition: .3s;
        cursor: pointer;
    }

    .image-box:hover {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, .06);
    }

    .image-box.filled {
        border-style: solid;
        border-color: rgba(34, 197, 94, .35);
    }

    .image-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .image-box .img-label {
        position: absolute;
        top: 6px;
        left: 6px;
        padding: 2px 8px;
        background: rgba(0, 0, 0, .7);
        color: #fff;
        border-radius: 20px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .05em;
        z-index: 2;
        backdrop-filter: blur(6px);
    }

    .image-box .img-label.main {
        background: linear-gradient(135deg, #2563eb, #22c55e);
    }

    /* Hidden file input overlay */
    .image-box input[type="file"] {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        cursor: pointer;
        z-index: 3;
        font-size: 0;
    }

    /* Empty state */
    .image-box .empty-state {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        gap: 6px;
        padding: 10px;
        text-align: center;
    }

    .image-box .empty-state svg,
    .image-box .empty-state .icon {
        font-size: 26px;
        color: #64748b;
    }

    .image-box .empty-state span {
        font-size: 10px;
        font-weight: 500;
    }

    /* Hover overlay for filled boxes */
    .image-box .overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, .65);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: .3s;
        color: #fff;
        gap: 5px;
        z-index: 2;
        pointer-events: none;
    }

    .image-box:hover .overlay { opacity: 1; }

    .image-box .overlay .icon {
        font-size: 22px;
        color: #22c55e;
    }

    .image-box .overlay span {
        font-size: 10px;
        font-weight: 600;
    }

    /* Button */
    .form button {
        width: 100%;
        height: 56px;
        border: none;
        border-radius: 14px;
        background: linear-gradient(135deg, #2563eb, #22c55e);
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: .35s;
        margin-top: 10px;
    }

    .form button:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 35px rgba(37, 99, 235, .4);
    }

    .form button:active { transform: translateY(-2px); }

    /* Back link */
    .back-link {
        display: block;
        text-align: center;
        color: #94a3b8;
        text-decoration: none;
        font-size: 13px;
        margin-top: 16px;
        transition: .3s;
    }

    .back-link:hover { color: #22c55e; }

    .back-link i { margin-right: 6px; }

    /* ========================================
        RESPONSIVE
    ======================================== */
    @media (max-width: 768px) {
        body { padding: 20px 12px; }
        .form { padding: 30px 24px; border-radius: 20px; }
        .form h2 { font-size: 26px; margin-bottom: 22px; }
        .form input[type="text"],
        .form input[type="number"] {
            height: 50px;
            font-size: 14px;
            margin-bottom: 12px;
        }
        .image-grid { gap: 10px; }
        .form button { height: 52px; font-size: 15px; }
    }

    @media (max-width: 480px) {
        body { padding: 15px 10px; align-items: flex-start; }
        .form { padding: 24px 18px; border-radius: 16px; }
        .form h2 { font-size: 22px; margin-bottom: 18px; }
        .form h2::after { width: 70px; height: 3px; }

        .section-label {
            font-size: 10px;
            margin: 16px 0 10px;
        }

        .form input[type="text"],
        .form input[type="number"] {
            height: 46px;
            font-size: 13px;
            padding: 0 14px;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .image-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .image-box { border-radius: 10px; }

        .image-box .img-label {
            font-size: 8px;
            padding: 2px 6px;
            top: 4px;
            left: 4px;
        }

        .image-box .empty-state .icon { font-size: 20px; }
        .image-box .empty-state span { font-size: 9px; }

        .form button {
            height: 48px;
            font-size: 14px;
            border-radius: 12px;
        }

        .back-link { font-size: 12px; }
    }
    </style>
</head>

<body>

    <div class="form">
        <h2>Edit Product</h2>

        <form method="POST" enctype="multipart/form-data">

            <!-- ===== PRODUCT INFO ===== -->
            <div class="section-label"><i class="fas fa-box"></i> Product Details</div>

            <input type="text"
                   name="product_name"
                   placeholder="Product Name"
                   value="<?php echo htmlspecialchars($row['product_name']); ?>"
                   required>

            <input type="number"
                   name="price"
                   placeholder="Price"
                   value="<?php echo htmlspecialchars($row['price']); ?>"
                   step="0.01"
                   required>

            <input type="text"
                   name="description"
                   placeholder="Description"
                   value="<?php echo htmlspecialchars($row['description']); ?>">

            <!-- ===== SELLER INFO ===== -->
            <div class="section-label"><i class="fas fa-user"></i> Seller Info</div>

            <input type="text"
                   name="seller_name"
                   placeholder="Seller Name"
                   value="<?php echo htmlspecialchars($row['seller_name']); ?>">

            <input type="text"
                   name="seller_number"
                   placeholder="Phone Number"
                   value="<?php echo htmlspecialchars($row['seller_number']); ?>">

            <input type="text"
                   name="seller_address"
                   placeholder="Address"
                   value="<?php echo htmlspecialchars($row['seller_address']); ?>">

            <!-- ===== 3 IMAGES ===== -->
            <div class="section-label"><i class="fas fa-images"></i> Product Images</div>

            <?php
                $img1 = !empty($row['image'])  ? $row['image']  : '';
                $img2 = !empty($row['image1']) ? $row['image1'] : '';
                $img3 = !empty($row['image2']) ? $row['image2'] : '';
            ?>

            <div class="image-grid">

                <!-- Image 1 (Main) -->
                <label class="image-box <?php echo $img1 ? 'filled' : ''; ?>">
                    <span class="img-label main">MAIN</span>
                    <?php if ($img1): ?>
                        <img src="uploads/<?php echo htmlspecialchars($img1); ?>" alt="Main Image">
                        <div class="overlay">
                            <span class="icon">📷</span>
                            <span>Change</span>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <span class="icon">📷</span>
                            <span>Upload</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" onchange="previewImg(this, 1)">
                </label>

                <!-- Image 2 -->
                <label class="image-box <?php echo $img2 ? 'filled' : ''; ?>">
                    <span class="img-label">2</span>
                    <?php if ($img2): ?>
                        <img src="uploads/<?php echo htmlspecialchars($img2); ?>" alt="Image 2">
                        <div class="overlay">
                            <span class="icon">📷</span>
                            <span>Change</span>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <span class="icon">📷</span>
                            <span>Upload</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image1" accept="image/*" onchange="previewImg(this, 2)">
                </label>

                <!-- Image 3 -->
                <label class="image-box <?php echo $img3 ? 'filled' : ''; ?>">
                    <span class="img-label">3</span>
                    <?php if ($img3): ?>
                        <img src="uploads/<?php echo htmlspecialchars($img3); ?>" alt="Image 3">
                        <div class="overlay">
                            <span class="icon">📷</span>
                            <span>Change</span>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <span class="icon">📷</span>
                            <span>Upload</span>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image2" accept="image/*" onchange="previewImg(this, 3)">
                </label>

            </div>

            <button type="submit" name="update">
                <i class="fas fa-save"></i> Update Product
            </button>

            <a href="user/user_product_edit.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>

        </form>
    </div>

    <script>
        // ✅ Live preview when user selects a new image
        function previewImg(input, index) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                const box = input.closest('.image-box');

                reader.onload = function(e) {
                    // Find existing img or create new one
                    let img = box.querySelector('img');
                    if (!img) {
                        img = document.createElement('img');
                        // Insert before overlay/empty-state
                        const overlay = box.querySelector('.overlay');
                        const empty = box.querySelector('.empty-state');
                        if (empty) empty.remove();
                        box.insertBefore(img, box.querySelector('input'));
                    }
                    img.src = e.target.result;

                    // Add filled class
                    box.classList.add('filled');

                    // Ensure overlay exists
                    if (!box.querySelector('.overlay')) {
                        const overlay = document.createElement('div');
                        overlay.className = 'overlay';
                        overlay.innerHTML = '<span class="icon">📷</span><span>Change</span>';
                        box.insertBefore(overlay, box.querySelector('input'));
                    }
                };

                reader.readAsDataURL(input.files[0]);
            }
        }

        // ✅ Prevent label click from triggering file dialog twice
        document.querySelectorAll('.image-box input[type="file"]').forEach(input => {
            input.addEventListener('click', e => e.stopPropagation());
        });
    </script>

</body>
</html>