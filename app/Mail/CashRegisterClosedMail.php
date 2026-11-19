<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de cierre de caja, con el detalle del arqueo adjunto en PDF
 * (el PDF se lee desde la ruta temporal).
 */
class CashRegisterClosedMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public string $pdfPath;

    public string $pdfName;

    public function __construct(array $data, string $pdfPath, string $pdfName = 'cierre-caja.pdf')
    {
        $this->data = $data;
        $this->pdfPath = $pdfPath;
        $this->pdfName = $pdfName;
    }

    public function build()
    {
        $subject = __('cash_register.register_closed_mail_subject', [
            'business' => $this->data['business_name'] ?? '',
        ]);

        $mail = $this->subject($subject)->view('emails.cash_register_closed');

        if (is_file($this->pdfPath)) {
            $mail->attach($this->pdfPath, [
                'as' => $this->pdfName,
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
