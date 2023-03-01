# Bplus-VTPay PHP API

Đây là thư viện cho VTMONEY đăng nhập tài khoản và sử dụng các chức năng như chuyển tiền lấy lịch sử giao dịch...vv
Thông tin tài khoản của bạn sẽ được lưu vào cơ sở dữ liệu

## Requirement*
    * Laravel >= 5
    * Guzzle *

## Basic Usage
    ```php
    <?php 
        $bplusData = bplusvtpay()->setBplusVTPay('username');;
        
        $result = $bplusData->loginWithPassword('12345');

        if ($result->success) {

            echo "Done!\n";
        }
    ?>
    ```
## Install the package
    * composer require vandatpiko/bplus-vtpay
