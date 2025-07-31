<?php

namespace App\Forms;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer 
{
    //
    public function sendResetPassToken(User $user, $token){
       
        $for = $order->team->company->display_name . '-' . $order->team->display_name;
        $from = User::find($order->user_id);
        $mail = new PHPMailer(true);
        $mail->Subject = 'Nova Conta '.$order->id . ' para ' . $for . ' validada ';
        $mail->Username = 'info@develop2you.com';             // SMTP username
        $mail->Password = 'LjCromo2019';                  // SMTP password
        $mail->setFrom('info@develop2you.com', 'Develop2You');
        $mail->addAddress($user->email, $user->name);
        /*$mails = User::where('company_id',$order->team->company_id)->orWhereNull('company_id')->get();
        foreach ($mails as $user) {
            if( ($user->id == $order->user_id) || ($user->hasRole('administrator')) ){
                $mail->addAddress($user->email, $user->name);
            }
        }*/
        $mail->Body = '<div>
        <p>Informamos que a encomenda '.$order->id . ' já se encontra validada.</p>
        <br>
        <p>Encomendada por: ' . $from->name . '</p>
        <p>Nota de Encomenda: "' . $order->description . '"</p><br>
        <div><p>Produtos: </p>
        <table><tr><th>Sku</th><th>Nome</th><th>Quantidade</th></tr>';

        foreach ($order->order_lines as $line) {
            if (null != $line->product)
                $mail->Body .= '<tr>
            <td>' . $line->product->sku . '</td>
            <td>' . $line->product->display_name . '</td>
            <td>' . $line->qty . '</td>
            </tr>';
        }
        $mail->Body .= '</table></div>
        <p>Clicar <a href="https:// /dashboard/orders/' . $order->id . '"> aqui</a> para ver informação sobre a encomenda.</p>
        </div>';

        return Mailer::sendEmail($mail);
    }

    public static function sendEmail($mail)
    {
     
        require '/vendor/autoload.php';                         // load Composer's autoloader
       

        /* try {*/
        // Server settings
        $mail->SMTPDebug = 0;                                    // Enable verbose debug output
        $mail->isSMTP();                                         // Set mailer to use SMTP
        $mail->Host = 'smtp.office365.com';                                                // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                                  // Enable SMTP authentication
        $mail->SMTPDebug = false;
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to
        $mail->CharSet = "UTF-8";
        //Recipients

        //$mail->addAddress($email, $name);    // Add a recipient, Name is optional
        //$mail->addReplyTo('info@cromotema.pt', 'Cromotema');

        //Attachments (optional)
        // $mail->addAttachment('/var/tmp/file.tar.gz');		// Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');	// Optional name

        //Content
        $mail->isHTML(true);                                                                     // Set email format to HTML
        $mail->Body    = "            
            <html><head>
            <style>
            .body{
                padding: 20px 30px;
                background-color: #f2f2f2;
                font-family: 'Livvic', sans-serif;
            }
            .header{
                text-align: center;
            }
            .header .logo{
                object-fit: contain;
                width: 300px;
            }
            .wrapper{
                text-align: justify;
                margin: 15px 50px 50px 50px;
                border-radius: 10px;
                padding: 50px 100px;
                background-color: #fff;
            }
            .content{
                width: fit-content;
                margin: auto;
                word-break: break-all;
            }
            .footer{
                text-align: center;
            }
        
            dt{
                font-weight: 600;
            }
            dd{
                margin-bottom: 15px;
            }
            @media only screen and (max-width: 768px) {
                .body{
                padding: 20px 30px;
                background-color: #f2f2f2;
                font-family: Tahoma, Geneva, sans-serif;
                }
                .wrapper{
                margin:0px;
                padding: 30px 20px;
                }
            }
            </style>
    </head><body class='body'>
    <div class='header'> <a href='https://www.develop2you.com'><img class='logo' src='/images/logo_1.png'></a></div>
    <div class='wrapper'><div class='content' > 
    " . $mail->Body . "
    </div></div>
    <div class='footer'>Este e-mail foi gerado automaticamente e não aceita respostas.</div>
    </body></html>";

        $mail->send();
        return true;

        //dd($mail);

        /*} catch (Exception $e) {
            return false;
        }*/
        //}
    }
}
