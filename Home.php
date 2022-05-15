<?php
    require_once("../../config/config.php");
    require_once("../../config/function.php");
    require_once("../../includes/login-admin.php");
    $title = 'DASHBROAD | '.$CMSNT->site('tenweb');
    require_once("../../public/admin/Header.php");
    require_once("../../public/admin/Sidebar.php");
    
    if(isset($_GET['huy']) && $getUser['level'] == 'admin')
    {
        $id = check_string($_GET['huy']);
        $row = $CMSNT->get_row("SELECT * FROM `card_auto` WHERE `id` = '$id' ");
        $getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `username` = '".$row['username']."' ");
        if(!$row)
        {
            admin_msg_error("Thẻ này không tồn tại trong hệ thống", BASE_URL('public/admin/Home.php'), 2000);
        }
        if($row['trangthai'] == 'hoantat')
        {
            admin_msg_error("Thẻ này đã được duyệt rồi", BASE_URL('public/admin/Home.php'), 2000);
        }
        $CMSNT->update("card_auto", [
            'trangthai' => 'thatbai',
            'ghichu'    => 'Thẻ cào không hợp lệ hoặc đã được sử dụng',
            'capnhat'   => gettime()
        ], " `id` = '$id' ");
        if(isset($row['callback']))
        {
            curl_get($row['callback']."?content=".$row['request_id']."&status=thatbai&thucnhan=0"."&menhgiathuc=0");
        }

        admin_msg_success("Hủy thành công!", BASE_URL('public/admin/Home.php'), 500);
    }
    if(isset($_GET['duyet']) && $getUser['level'] == 'admin')
    {
        $id = check_string($_GET['duyet']);
        $row = $CMSNT->get_row("SELECT * FROM `card_auto` WHERE `id` = '$id' ");
        $getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `username` = '".$row['username']."' ");

        if(!$row)
        {
            admin_msg_error("Thẻ này không tồn tại trong hệ thống", BASE_URL('public/admin/Home.php'), 2000);
        }
        if($row['trangthai'] == 'hoantat')
        {
            admin_msg_error("Thẻ này đã được duyệt rồi", BASE_URL('public/admin/Home.php'), 2000);
        }

        $CMSNT->update("card_auto", [
            'trangthai' => 'hoantat',
            'capnhat'   => gettime()
        ], " `id` = '$id' ");

        $CMSNT->cong("users", "money", $row['thucnhan'], " `username` = '".$row['username']."' ");
        $CMSNT->cong("users", "total_money", $row['thucnhan'], " `username` = '".$row['username']."' ");
        /* CẬP NHẬT DÒNG TIỀN */
        $CMSNT->insert("dongtien", array(
            'sotientruoc'   => $getUser['money'],
            'sotienthaydoi' => $row['thucnhan'],
            'sotiensau'     => $getUser['money'] + $row['thucnhan'],
            'thoigian'      => gettime(),
            'noidung'       => 'Đổi thẻ seri ('.$row['seri'].')',
            'username'      => $getUser['username']
        ));
        if(isset($row['callback']))
        {
            curl_get($row['callback']."?content=".$row['request_id']."&status=hoantat&thucnhan=".$row['thucnhan']."&menhgiathuc=".$row['menhgia']);
        }

        admin_msg_success("Duyệt thành công!", BASE_URL('public/admin/Home.php'), 500);
    }
?>





<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>MOD CHACHGM</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <button class="btn btn-primary" type="button" data-toggle="modal"
                            data-target="#modal-default">THÔNG BÁO</button>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="alert alert-dark">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <b>Thông tin cập nhật</b>
            <ul>
                <li>22/12/2021: Taoshoptudong.xyz </li>
                <li>22/12/2021:Chạch gaming </li>
            </ul>
            <p>Mua code ủng hộ tác giả tại đây: <a target="_blank" href="https://Zalo.me/0355605502">Cập nhật Tại CHẠCH GM</a></p>
        </div>0
        <div id="thongbao"></div>
        <div class="row">
            <div class="col-lg-3 col-12">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="total_users"><?=$CMSNT->num_rows("SELECT * FROM `users` ");?></h3>
                        <p>Tổng thành viên</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-12">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="total_money">
                            <?=format_cash($CMSNT->get_row("SELECT SUM(`money`) FROM `users` WHERE `money` > 0 ")['SUM(`money`)']);?>đ
                        </h3>
                        <p>Tổng số dư thành viên</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-12">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="the_hop_le">
                            <?=format_cash($CMSNT->num_rows("SELECT * FROM `card_auto` WHERE `trangthai` = 'hoantat' "));?>
                        </h3>
                        <p>Thẻ cào hợp lệ</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-12">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="tong_tien_the">
                            <?=format_cash($CMSNT->get_row("SELECT SUM(`menhgia`) FROM `card_auto` WHERE `trangthai` = 'hoantat' ")['SUM(`menhgia`)']);?>đ
                        </h3>
                        <p>Tổng tiền thẻ</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-12">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3 id="doanh_thu_today">
                            <?=format_cash($CMSNT->get_row("SELECT SUM(`menhgia`) FROM `card_auto` WHERE `trangthai` = 'hoantat' AND `thoigian` >= DATE(NOW()) AND `thoigian` < DATE(NOW()) + INTERVAL 1 DAY ")['SUM(`menhgia`)']);?>đ
                        </h3>
                        <p>DOANH THU HÔM NAY</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-12">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3 id="san_luong_today">
                            <?=format_cash($CMSNT->num_rows("SELECT * FROM `card_auto` WHERE `trangthai` = 'hoantat' AND `thoigian` >= DATE(NOW()) AND `thoigian` < DATE(NOW()) + INTERVAL 1 DAY "));?>
                        </h3>
                        <p>SẢN LƯỢNG HÔM NAY</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-12">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3 id="total_web_api">
                            <?=format_cash($CMSNT->num_rows("SELECT DISTINCT `callback` FROM `card_auto` WHERE `callback` IS NOT NULL "));?>
                        </h3>
                        <p>WEBSITE ĐANG ĐẤU API</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-12">
                <!-- small box -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3 id="loi_nhuan_today">
                            <?=format_cash($CMSNT->get_row("SELECT SUM(`amount`) FROM `card_auto` WHERE `trangthai` = 'hoantat' AND `thoigian` >= DATE(NOW()) AND `thoigian` < DATE(NOW()) + INTERVAL 1 DAY ")['SUM(`amount`)'] - $CMSNT->get_row("SELECT SUM(`thucnhan`) FROM `card_auto` WHERE `trangthai` = 'hoantat' AND `thoigian` >= DATE(NOW()) AND `thoigian` < DATE(NOW()) + INTERVAL 1 DAY ")['SUM(`thucnhan`)']);?>đ
                        </h3>
                        <p>LỢI NHUẬN HÔM NAY</p>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
            function GetUsageServer() {
                $.ajax({
                    url: "<?=BASE_URL('assets/ajaxs/realtime_dashboard.php');?>",
                    method: "GET",
                    dataType: "JSON",
                    success: function(data) {
                        $('#total_money').text(data.total_money);
                        $('#total_users').text(data.total_users);
                        $('#the_hop_le').text(data.the_hop_le);
                        $('#tong_tien_the').text(data.tong_tien_the);
                        $('#doanh_thu_today').text(data.doanh_thu_today);
                        $('#san_luong_today').text(data.san_luong_today);
                        $('#total_web_api').text(data.total_web_api);
                        $('#loi_nhuan_today').text(data.loi_nhuan_today);

                    }
                });

            }
            setInterval(function() {
                $('#thongbao').load(GetUsageServer());
            }, 2000);
            </script>
            <section class="col-lg-12 connectedSortable">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            DÒNG TIỀN GẦN ĐÂY
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn bg-success btn-sm" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn bg-warning btn-sm" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i>
                            </button>
                            <button type="button" class="btn bg-danger btn-sm" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>USERNAME</th>
                                        <th>SỐ TIỀN TRƯỚC</th>
                                        <th>SỐ TIỀN THAY ĐỔI</th>
                                        <th>SỐ TIỀN HIỆN TẠI</th>
                                        <th>THỜI GIAN</th>
                                        <th>NỘI DUNG</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 0;
                                    foreach($CMSNT->get_list(" SELECT * FROM `dongtien` ORDER BY id DESC LIMIT 500 ") as $row){
                                    ?>
                                    <tr>
                                        <td><?=$i++;?></td>
                                        <td><a
                                                href="<?=BASE_URL('Admin/User/Edit/'.getUser($row['username'], 'id'));?>"><?=$row['username'];?></a>
                                        </td>
                                        <td><?=format_cash($row['sotientruoc']);?></td>
                                        <td><?=format_cash($row['sotienthaydoi']);?></td>
                                        <td><?=format_cash($row['sotiensau']);?></td>
                                        <td><span class="badge badge-dark"><?=$row['thoigian'];?></span></td>
                                        <td><?=$row['noidung'];?></td>
                                    </tr>
                                    <?php }?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>STT</th>
                                        <th>USERNAME</th>
                                        <th>SỐ TIỀN TRƯỚC</th>
                                        <th>SỐ TIỀN THAY ĐỔI</th>
                                        <th>SỐ TIỀN HIỆN TẠI</th>
                                        <th>THỜI GIAN</th>
                                        <th>NỘI DUNG</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
            <section class="col-lg-12 connectedSortable">
                <section class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            THẺ NẠP GẦN ĐÂY
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn bg-success btn-sm" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button type="button" class="btn bg-warning btn-sm" data-card-widget="maximize"><i
                                    class="fas fa-expand"></i>
                            </button>
                            <button type="button" class="btn bg-danger btn-sm" data-card-widget="remove">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="datatable1" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>SERVER</th>
                                        <th>USERNAME</th>
                                        <th>LOẠI THẺ</th>
                                        <th>MỆNH GIÁ</th>
                                        <th>THỰC NHẬN</th>
                                        <th>SERI</th>
                                        <th>PIN</th>
                                        <th>THỜI GIAN</th>
                                        <th>CẬP NHẬT</th>
                                        <th>TRẠNG THÁI</th>
                                        <th>CALLBACK</th>
                                        <th>THAO TÁC</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 0; foreach($CMSNT->get_list(" SELECT * FROM `card_auto` ORDER BY id DESC LIMIT 500 ") as $row){ ?>
                                    <tr>
                                        <td><?=$i++;?></td>
                                        <td><?=$row['server'];?></td>
                                        <td><a
                                                href="<?=BASE_URL('Admin/User/Edit/'.getUser($row['username'], 'id'));?>"><?=$row['username'];?></a>
                                        </td>
                                        <td><?=$row['loaithe'];?></td>
                                        <td><b style="color: green;"><?=$row['menhgia'];?></b></td>
                                        <td><b style="color: red;"><?=$row['thucnhan'];?></b></td>
                                        <td><?=$row['seri'];?></td>
                                        <td><?=$row['pin'];?></td>
                                        <td><span class="label label-danger"><?=$row['thoigian'];?></span></td>
                                        <td><span class="label label-info"><?=$row['capnhat'];?></span></td>
                                        <td><?=status_admin($row['trangthai']);?></td>
                                        <td><?=$row['callback'];?></td>
                                        <td>
                                            <a class="btn btn-info" type="button"
                                                href="<?=BASE_URL('public/admin/Home.php?duyet='.$row['id']);?>">DUYỆT</a>
                                            <a class="btn btn-danger" type="button"
                                                href="<?=BASE_URL('public/admin/Home.php?huy='.$row['id']);?>">HỦY</a>
                                            <?php if($row['server'] == '') { ?><a class="btn btn-primary" type="button"
                                                href="<?=BASE_URL('public/admin/EditCard.php?id='.$row['id']);?>">EDIT</a><?php }?>
                                        </td>
                                    </tr>
                                    <?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
        </div>

</div>
</section>
<!-- /.content -->
</div>



<div class="modal fade" id="modal-default">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Thông báo</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Web tạo shop giá rẻ <b style="color: blue;">Taoshoptudong.xyz</b> Siêu xịn
                    <b
                        style="color:red;">Thank you!!!</b>.
                </p>
                <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi</p>

            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-default" data-dismiss="modal">Hủy</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<?php if($new_version != $config['version']) { ?>
<script type="text/javascript">
$(document).ready(function() {
    setTimeout(e => {
        showlog()
    }, 1000)
});

function showlog() {
    $('#modal-default').modal({
        keyboard: true,
        show: true
    });
}
</script>
<?php }?>

<!-- ĐƠN VỊ THIẾT KẾ WEB WWW.CMSNT.CO | ZALO: 0947838128 | FACEBOOK: FB.COM/NTGTANETWORK -->
<script type="text/javascript">
$("#update").on("click", function() {
    $('#update').html(
        'Đang tải bản cập nhật <div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>'
    ).prop('disabled',
        true);
    $.ajax({
        url: "<?=BASE_URL("Update.php");?>",
        method: "POST",
        data: {
            type: 'update'
        },
        success: function(response) {
            $("#thongbao").html(response);
            $('#update').html(
                    'Cập nhật ngay')
                .prop('disabled', false);
        }
    });
});
</script>

<script>
$(function() {
    $("#datatable").DataTable({
        "responsive": false,
        "autoWidth": false,
    });
    $("#datatable1").DataTable({
        "responsive": false,
        "autoWidth": false,
    });
});
</script>

<?php 
    require_once("../../public/admin/Footer.php");
?>