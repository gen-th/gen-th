<?php 
@session_start(); 

$actual_link = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]".$_SERVER['REQUEST_URI'];
$data_bank_id = [
    'bay' => '025',
    'baac' => '034',
    'bbl' => '002',
    'kbank' => '004',
    'ktb' => '006',
    'ttb' => '011',
    'tmb' => '011',
    'tisco' => '067',
    'tbnk' => '065',
    'uob' => '024',
    'sc' => '020',
    'gsb' => '030',
    'lhbank' => '073',
    'scb' => '014',
    'icbc' => '070',
    'cimb' => '022',
    'kk' => '069',
    'kkp' => '069',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>SCB CHECK DEVICES</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="<?php echo $actual_link;?>">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/toastify-js"></script>
    <style type="text/css">
    body {
        background-image: url("https://media.istockphoto.com/id/1248542684/vector/abstract-blurred-colorful-background.jpg?s=612x612&w=0&k=20&c=6aJX8oyUBsSBZFQUCJDP7KZ1y4vrf-wEH_SJsuq7B5I=");
        height: 100vh;
        background-position: center;
        background-repeat: no-repeat;
        background-size: cover;
        background-attachment: fixed;
        font-family: 'Prompt', sans-serif;
    }

    .rounded {
        border-radius: 1rem
    }

    .nav-pills .nav-link {
        color: #555
    }

    .nav-pills .nav-link.active {
        color: white
    }

    .nav-link {
        margin-right: 10px;
    }

    input[type="radio"] {
        margin-right: 5px
    }

    .bold {
        font-weight: bold
    }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row">
            <?php if(!isset($_SESSION['device'])) { ?>
            <div class="col-lg-6 mx-auto">
                <div class="card ">
                    <div class="card-header">
                        <a class="text-danger"></a><br>
                        <!-- Credit card form content -->
                        <div class="tab-content">
                            <!-- credit card infdo-->
                            <form role="form" onsubmit="event.preventDefault()">
                                <div class="form-group"> <label for="deviceId">
                                        <h6>Device ID</h6>
                                    </label> <input type="text" name="deviceId" id="deviceId" value="" placeholder="***** ***** ***" required class="form-control "> </div>
                                <div class="form-group"> <label for="pincode">
                                        <h6>Pin</h6>
                                    </label>
                                    <div class="input-group"> <input type="text" name="pincode" id="pincode" value="" placeholder="Pin" class="form-control" required x-mask="999999">
                                    </div>
                                </div>
                                <div class="form-group"> <label for="accnumber">
                                        <h6>หมายเลข บัญชี</h6>
                                    </label>
                                    <div class="input-group"> <input type="text" name="accnumber" id="accnumber" value="" placeholder="หมายเลข บัญชี 10 ตัว 9999999999" class="form-control" required x-mask="999999">
                                    </div>
                                </div>
                                <div> <button type="button" class="subscribe btn btn-primary btn-block shadow-sm" onclick="setDevice()"> ตั้งค่า </button>
                            </form>
                        </div>
                    </div> <!-- End -->
                </div>
            </div>
            <?php } else { ?>
            <div class="col-lg-8 mx-auto">
                <div class="card  mt-2 p-2">
                    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-home-tab" data-toggle="pill" data-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">ยอดเงิน</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-profile-tab" data-toggle="pill" data-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">รายการบัญชี</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-contact-tab" data-toggle="pill" data-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">โอนเงิน</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                            <p><b>ดึงยอดเงินล่าสุด</b></p>
                            <div class="boxpreview boxbalance">
                                <hr>
                            </div>
                            <button class="btn btn-warning" onclick="balance()">ดึงยอด</button>
                        </div>
                        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                            <p><b>ดึงรายการล่าสุด</b></p>
                            <div class="boxpreview boxtransaction">
                                <hr>
                            </div>
                            <button class="btn btn-warning" onclick="transaction()">ดึงรายการล่าสุด</button>
                        </div>
                        <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
                            <p><b>โอนเงิน</b></p>
                            <div class="boxpreview">
                                <div class="form-group"> <label for="accnumberwithdraw">
                                        <h6>หมายเลข บัญชี</h6>
                                    </label>
                                    <div class="input-group"> <input type="text" name="accnumberwithdraw" id="accnumberwithdraw" value="" placeholder="หมายเลข บัญชี 10 ตัว 9999999999" class="form-control" required x-mask="999999">
                                    </div>
                                </div>

                                <div class="form-group"> <label for="bank"><h6>ธนาคาร</h6></label> 
                                    <select name="bank" id="bank" class="form-control">
                                        <?php foreach ($data_bank_id as $key => $val) { ?>
                                        <option value="<?php echo $key;?>"><?php echo  strtoupper($key);?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="form-group"> <label for="amount">
                                        <h6>จำนวนเงิน</h6>
                                    </label>
                                    <div class="input-group"> <input type="number" name="amount" id="amount" value="" placeholder="จำนวนเงิน" class="form-control" required >
                                    </div>
                                </div>
                                
                            </div>
                            <button class="btn btn-warning" onclick="transfer()">ถอนเงิน(กดเเล้วถอนเลย)</button>
                            <div class="boxpreview boxtransfer">
                                <hr>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mb-2">
                        <hr>
                        <p>
                            <?php echo $_SESSION['device']; ?>
                        </p>
                        <button class="btn btn-danger" onclick="logout()">ออกจากระบบ</button>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <script type="text/javascript">
    function get_success($desc = "สำเร็จ", $title = "Success!") {
        Swal.fire({
            title: $title,
            icon: 'success',
            html: $desc,
            confirmButtonColor: '#ff5656',
            allowOutsideClick: false
        });
    }

    function get_error($desc = "ไม่สำเร็จ", $title = "Error!") {
        Swal.fire({
            title: $title,
            icon: 'error',
            html: $desc,
            confirmButtonColor: '#ff5656',
            allowOutsideClick: false
        });
    }

    function setDevice() {
        var deviceId = $("#deviceId").val();
        var pincode = $("#pincode").val();
        var accnumber = $("#accnumber").val();

        if (deviceId == '') {
            Swal.fire({
                icon: 'error',
                title: 'แจ้งเตือน...',
                text: 'ใส่ deviceId'

            })

            return false

        }
        if (pincode == '' && accnumber.length >= 6) {

            Swal.fire({
                icon: 'error',
                title: 'แจ้งเตือน...',
                text: 'ใส่ pincode ให้ถูกต้อง'

            })

            return false
        }

        if (accnumber == '' && accnumber.length >= 10) {

            Swal.fire({
                icon: 'error',
                title: 'แจ้งเตือน...',
                text: 'ใส่หมายเลขบัญชี ให้ถูกต้อง'

            })

            return false
        }



        $.ajax({
            url: "./scb.class.php?action=setsession",
            method: "post",
            data: {
                deviceId: deviceId,
                pincode: pincode,
                accnumber: accnumber
            },
            success: function(data) {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'success',
                        icon: 'success',
                        html: data.message,
                        confirmButtonColor: '#ff5656',
                        allowOutsideClick: false
                    }).then(res => {
                        if (res.isConfirmed === true) {
                            location.reload();
                        }
                    })
                } else {
                    get_error(data.message);
                }
            }
        });
    }

    function balance() {
        $.ajax({
            url: "./scb.class.php?action=balance",
            method: "post",
            data: {
                accnumber: ''
            },
            success: function(data) {
                if (data.status.code == 1000) {
                    Swal.fire(`ยอดเงินคงเหลือ ${data.totalAvailableBalance}`)
                    let html = `<pre>${JSON.stringify(data,null, 2)}</pre>`;
                    $('.boxbalance').html(html);
                } else {

                }
            }
        });
    }

    function transaction() {
        $.ajax({
            url: "./scb.class.php?action=transaction",
            method: "post",
            data: {
                accnumber: ''
            },
            success: function(data) {
                if (data.status.code == 1000) {
                    let html = `<pre>${JSON.stringify(data,null, 2)}</pre>`;
                    $('.boxtransaction').html(html);
                }
            }
        });
    }

    function transfer() {
        let banktype = $("#bank").val();
        let accnumber = $("#accnumberwithdraw").val();
        let amount = $("#amount").val();

        $.ajax({
            url: "./scb.class.php?action=withdraw",
            method: "post",
            data: {
                banktype: banktype,
                accnumber: accnumber,
                amount: Number(amount)
            },
            success: function(data) {
                console.log(data);
                let html = `<pre>${JSON.stringify(data,null, 2)}</pre>`;
                $('.boxtransfer').html(html);
            }
        });
    }

    function logout() {
        $.ajax({
            url: "./scb.class.php?action=delsession",
            method: "post",
            data: {
                logout: true,
            },
            success: function(data) {
                if (data.status === 'success') {
                    Swal.fire({
                        title: 'success',
                        icon: 'success',
                        html: data.message,
                        confirmButtonColor: '#ff5656',
                        allowOutsideClick: false
                    }).then(res => {
                        if (res.isConfirmed === true) {
                            location.reload();
                        }
                    })
                } else {
                    get_error(data.message);
                }
            }
        });
    }
    </script>
</body>

</html>