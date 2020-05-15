<?php 
    session_start();
    define('isSet', 1);
    define('setting', 1);
    require('settings.php');
    require('db_connect.php');
    if (!isset($installed)) {
        die("You must run the installation file (install.php) in the admin directory in order to run this file.");
    }
    // Kiểm tra đăng nhập
    require_once('../loginCheck.php');
    loginCheck($wdc_id, $wdc_token, $db, true); // kiểm tra đăng nhập
?>

<?php 
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $typeRequest = $_GET['type'];
    $isDelete = $_GET['delete'];
    // Nếu chuyển hướng từ create
    if ($_GET['rdfrom']=='create') {
        $success = "Create new $typeRequest successfully";
    }
    $id = $_GET['id'];
    $issubmit = $_GET['issubmit'];
    // Form Date()
    switch ($typeRequest) { // lấy dữ liệu từ db
        case 'post':
            if ($db->selectValue('posts', "id=$id", 'title', 'content', 'date')!==false) {
                $dataFromDb = mysqli_fetch_assoc($db->selectValue('posts', "id=$id", 'title', 'content', 'author', 'date'));
            } else {
                $dataFromDb = null;
            }
            break;
        case 'page':
            if ($db->selectValue('pages', "id=$id", 'title', 'content', 'date')!==false) {
                $dataFromDb = mysqli_fetch_assoc($db->selectValue('pages', "id=$id", 'title', 'content', 'author', 'date'));
            } else {
                $dataFromDb = null;
            }
            break;
        case 'category':
            if ($db->selectValue('categories', "id=$id", 'title', 'content', 'date')!==false) {
                $dataFromDb = mysqli_fetch_assoc($db->selectValue('categories', "id=$id", 'title', 'content', 'author', 'date'));
            } else {
                $dataFromDb = null;
            }
            break;
    }
    if ($dataFromDb!==null){ // nếu dữ liệu trả về ko là null
        $fdate = date('d', strtotime($dataFromDb['date']));
        $fmonth = date('m', strtotime($dataFromDb['date']));
        $fyear = date('Y', strtotime($dataFromDb['date']));
        $fhour = date('H', strtotime($dataFromDb['date']));
        $fminute = date('i', strtotime($dataFromDb['date']));
        $fsecond = date('s', strtotime($dataFromDb['date']));
        $title = $dataFromDb['title'];
        $content = $dataFromDb['content'];
    } else { // nếu ko
        $error = "Unexpected error, $typeRequest not found!";
    }
    
    function selectMonth($m){
        return "$('#wdc_emonth [value=$m]').attr('selected','selected');";
    }

    $fayear = $_POST['year'];
    $famonth = $_POST['month'];
    $fadate = $_POST['date'];
    $fahour = $_POST['hour'];
    $faminute = $_POST['minute'];
    $fasecond = $_POST['second'];

    if (isset($fayear)&&isset($famonth)&&isset($fadate)&&isset($fahour)&&isset($faminute)&&isset($fasecond)) {
        $fullDate = "$fayear-$famonth-$fadate $fahour:$faminute:$fasecond";
    } else {
        $fullDate = null;
    }
    $dom = new DOMDocument();
    // Process the user content:
    function errorTemplate($error)
    {
        if (isset($error)) {
            return "<div class='alert alert-danger' role='alert'>".$error."</div>";
        }
    }
    function successTemplate($success)
    {
        if (isset($success)) {
            return "<div class='alert alert-success' role='alert'>".$success."</div>";
        }
    }
    if ($issubmit == 'yes' && !$isDelete) {
        if (!$_POST['title']=='') { // Nếu title đã được nhập
            $title = $_POST['title']; // title
            if (!$_POST['content']=='') {
                // if (isset)
                $content = $_POST['content']; // content
                if (isset($fullDate)) {
                    // Gửi dữ liệu bài viết đến CSDL
                    switch ($typeRequest) {
                        case 'post':
                            if (isset($_POST['category'])) { // nếu đã chọn
                                $categoryId = $_POST['category']; // id của category
                                $categoryResult = mysqli_fetch_assoc($db->selectValue('categories', "id = '$categoryId'", 'slug')); // lấy kết quả từ db
                                $category = $categoryResult['slug']; // category là category slug
                            } else { // nếu ko
                                $category = 'uncategorized';
                            }
                            // chỉnh sửa bài viết
                            $db->editValue('posts', "id=$id", 'title', "'$title'");
                            $db->editValue('posts', "id=$id", 'category', "'$category'");
                            $db->editValue('posts', "id=$id", 'content', "'$content'");
                            $db->editValue('posts', "id=$id", 'date', "'$fullDate'");
                            if (isset($_POST['author'])) {
                                $author = $_POST['author'];
                                $db->editValue('posts', "id=$id", 'author', "'$author'");
                            }
                            break;
                        
                        case 'page':
                            // chỉnh sửa trang
                            $db->editValue('pages', "id=$id", 'title', "'$title'");
                            $db->editValue('pages', "id=$id", 'content', "'$content'");
                            $db->editValue('pages', "id=$id", 'date', "'$fullDate'");
                            if (isset($_POST['author'])) {
                                $author = $_POST['author'];
                                $db->editValue('pages', "id=$id", 'author', "'$author'");
                            }
                            break;

                        case 'category':
                            // chỉnh sửa chuyên mục
                            $db->editValue('categories', "id=$id", 'title', "'$title'");
                            $db->editValue('categories', "id=$id", 'content', "'$content'");
                            $db->editValue('categories', "id=$id", 'date', "'$fullDate'");
                            if (isset($_POST['author'])) {
                                $author = $_POST['author'];
                                $db->editValue('categories', "id=$id", 'author', "'$author'");
                            }
                            break;
                    }
                    // Lấy giá trị mới đưa vào
                    $success = "Edit $typeRequest successfully!";
                } else {
                    // nếu ko thì báo lỗi
                    $error = 'You must fill out which time to create this!';
                }
            } else {
                $error = 'You must fill out the content!';
            }
        } else {
            $error = 'You must fill out the title!';
        }
    } elseif ($isDelete == true) {
        switch ($typeRequest) {
            case 'post':
                $db->deleteFromTable('posts', "id=$id");
                header("Location: $site_addr/admin/view.php?type=posts&rdfrom=editrm", true, 303);
                break;
            
            case 'page':
                $db->deleteFromTable('pages', "id=$id");
                header("Location: $site_addr/admin/view.php?type=pages&rdfrom=editrm", true, 303);
                break;

            case 'category':
                if ($id!=1) {
                    $db->deleteFromTable('categories', "id=$id");
                    header("Location: $site_addr/admin/view.php?type=categories&rdfrom=editrm", true, 303);
                } else {
                    $error = 'This category is the default category, you cannot remove it!';
                }
                break;
        }
        
    }
    $formAction = "edit.php?type=$typeRequest&id=$id&issubmit=yes";
?>

<?php require_once(__DIR__.'/themes/default/header.php'); ?>

<?php require_once(__DIR__.'/themes/default/modules/mainMenus.php') ?>

<?php 
// lấy dữ liệu từ db
$postCategoryResultFDb = mysqli_fetch_assoc($db->selectValue('posts', "id=$id", 'category'));
$postCategory = $postCategoryResultFDb['category'];
$categories = $db->selectCol('categories', 'id', 'title');
$resultCategoryToSelect = mysqli_fetch_assoc($db->selectValue('categories', "slug='$postCategory'", 'id'));
$categoryToSelect = $resultCategoryToSelect['id']; // bài viết đang ở chuyên mục nào
$categoriesResults = mysqli_fetch_all($categories);
$categoryOptions = "<input type='radio' name='category' value='1' id='category-1'> <label for='category-1'>Uncategorized</label> <br>";
for ($i=1; $i < count($categoriesResults); $i++) { 
    for ($j=0; $j < count($categoriesResults[$i]); $j++) { 
        if ($j==0){
            $val = $categoriesResults[$i][0];
        } else {
            $name = $categoriesResults[$i][$j];
            $categoryOptions .= "<input type='radio' name='category' value='$val' id='category-$val'> <label for='category-$val'>$name</label> <br>";
        }
    }
}
$authorSubmit = false;
if (isset($_POST['author'])) {
    $authorSubmit = true;
}
$authorResults = mysqli_fetch_all($db->selectValue('users_permision', "admincp='yes'", 'username'));
for ($i=0; $i < count($authorResults); $i++) { 
    $authorOption .="<option value='".$authorResults[$i][0]."'>".$authorResults[$i][0]."</option>";
}

$authorSelector = 
"<div class='form-group'>
    <label for='author'>Select author from list</label>
    <select class='form-control' id='author' name='author'>
        $authorOption
    </select>
</div>";

$createOn = 
"<h5>When</h5>
<input type='number' name='date' id='wdc_edate' min='0' max='31' value='$fdate'>
<select name='month' id='wdc_emonth'>
    <option value='01'>01 - Jan</option>
    <option value='02'>02 - Feb</option>
    <option value='03'>03 - Mar</option>
    <option value='04'>04 - Apr</option>
    <option value='05'>05 - May</option>
    <option value='06'>06 - Jun</option>
    <option value='07'>07 - Jul</option>
    <option value='08'>08 - Aug</option>
    <option value='09'>09 - Sep</option>
    <option value='10'>10 - Oct</option>
    <option value='11'>11 - Nov</option>
    <option value='12'>12 - Dec</option>
</select>
<input type='number' name='year' id='wdc_eyear' min='0' max='9999' value='$fyear'>
<br />
<span>at </span><input type='number' name='hour' id='wdc_ehour' min='0' max='23' value='$fhour'><span>:</span><input type='number' name='minute' id='wdc_emin' min='0' max='59' value='$fminute'><span>:</span><input type='number' name='second' id='wdc_esec' min='0' max='59' value='$fsecond'>";
    $js = '<script>'.selectMonth($fmonth);
    $js .= "let collapseCol = false;
    $('#wdc_admin_create > ul').addClass('wdc_submenu_01');
    $('#wdc_collapseActivate').on('click', function (){
        switch (collapseCol) {
            case false:
            $('#wdc_admin_create > ul').removeClass('wdc_submenu_01');
            collapseCol = true;
            break;

            default:
            $('#wdc_admin_create > ul').addClass('wdc_submenu_01');
            collapseCol = false;
        break;}
    });
    $('#delete').on('click', function (){
        Swal.fire({
            title: 'Are you sure?',
            text: 'You won\'t be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
                if (result.value) {
                    Swal.fire(
                        'Deleted!',
                        'The $typeRequest has been deleted.',
                        'success'
                    )
                    window.location.assign('edit.php?type=$typeRequest&id=$id&delete=true');
                }
        })
    });
    $('#category-$categoryToSelect').attr('checked','checked');
    $(`[value='".(($authorSubmit) ? $_POST['author'] : $dataFromDb['author'])."']`).attr('selected','selected');
    $(function () {
        tinymce.get('textarea').on('keyup', function(e) {
            content = tinymce.get('textarea').getContent();
            if (content != '') {
                window.onbeforeunload = function(e){
                    e.returnValue = 'Information entered will be lost forever!';
                };
            }
        });
    });
    ";
    $js .= '</script>';
    $invalidRequest =
"<main><h1>Invalid request, please try again!</h1><main>";
    $htmlEditPost = 
"<main>
    <form method='POST' action='$formAction'>
        <div class='container'>
            <div class='row'>
                <div class='col'>
                    <h2>Edit post</h2>
                    ".successTemplate($success).errorTemplate($error)."
                    <input type='text' class='form-control' name='title' value='$title'>
                </div>
            </div>
            <div class='row'>
                <div class='col-8'>
                    <textarea id='textarea' name='content'>$content</textarea>
                </div>
                <div class='col-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h5 class='card-title'>Configuration</h5>
                            $createOn
                            <h5>Author</h5>
                            $authorSelector
                            <h5>Category</h5>
                            $categoryOptions
                            <br>
                            <span><button id='edit' class='btn btn-info' type='submit'>Save</button><div class='btn btn-danger' id='delete'>Delete</div></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>";
$htmlEditPage = 
"<main>
    <form method='POST' action='$formAction'>
        <div class='container'>
            <div class='row'>
                <div class='col'>
                    <h2>Edit page</h2>
                    ".successTemplate($success).errorTemplate($error)."
                    <input type='text' class='form-control' name='title' value='$title'>
                </div>
            </div>
            <div class='row'>
                <div class='col-8'>
                    <textarea id='textarea' name='content'>$content</textarea>
                </div>
                <div class='col-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h5 class='card-title'>Configuration</h5>
                            $createOn
                            <h5>Author</h5>
                            $authorSelector
                            <br>
                            <span><button id='edit' class='btn btn-info' type='submit'>Save</button><div class='btn btn-danger' id='delete'>Delete</div></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>";
    $htmlEditCategory = 
"<main>
    <form method='POST' action='$formAction'>
        <div class='container'>
            <div class='row'>
                <div class='col'>
                    <h2>Edit category</h2>
                    ".successTemplate($success).errorTemplate($error)."
                    <input type='text' class='form-control' name='title' value='$title'>
                </div>
            </div>
            <div class='row'>
                <div class='col-8'>
                    <textarea id='textarea' name='content'>$content</textarea>
                </div>
                <div class='col-4'>
                    <div class='card'>
                        <div class='card-body'>
                            <h5 class='card-title'>Configuration</h5>
                            $createOn
                            <h5>Author</h5>
                            $authorSelector
                            <br>
                            <span><button id='edit' class='btn btn-info' type='submit'>Save</button><div class='btn btn-danger' id='delete'>Delete</div></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</main>";

$categoryDoNotDelete = 
"";

    if (!$dataFromDb==null) { // kiểm tra xem dữ liệu trả về có null ko
        switch ($typeRequest) {
            case 'post':
                echo $htmlEditPost;
                break;
            
            case 'page':
                echo $htmlEditPage;
                break;
            case 'category';
                echo $htmlEditCategory;
                break;
            default:
            echo $invalidRequest;
            break;
        }
    } else { // nếu là null
        echo "<main>".errorTemplate($error)."</main>";
    }
?>

<?php require_once(__DIR__.'/themes/default/footer.php');echo $js?>