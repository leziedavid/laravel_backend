<?php

namespace App\Models;

use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Invoice extends Model
{
    use HasFactory;

    public static function getInvoice($id)
    {
        $pdf = new Fpdf;

        $orders = DB::table('orders')
            ->join('order_product', 'order_product.order_id', '=', 'orders.id_orders')
            ->join('products', 'products.id_products', '=', 'order_product.product_id')
            ->where('orders.id_orders', '=', $id)
            ->orderBy('order_product.order_id', 'desc')
            ->distinct()
            ->get();

        $ordersData = DB::table('orders')
            ->where('orders.id_orders', '=', $id)
            ->orderBy('orders.id_orders', 'desc')
            ->distinct()
            ->get();

        $pdf->AliasNbPages();
        $pdf->SetMargins(3, 0);
        $pdf->SetFont('Times', 'B',  16);
        $pdf->AddPage();
        $pdf->Ln(20);
        $filelogo = public_path('images/logos2.png');
        $pdf->Image($filelogo, 2, 5, 25, 25);

        $pdf->SetXY(15, 5);
        $pdf->SetFont('Times', '',  12);
        $pdf->Cell(176, 5, utf8_decode('ENTREPRISE TARAFE'), 0, 0, 'C');
        $pdf->Ln();
        $pdf->SetXY(15, 10);
        $pdf->SetFont('Times', '',  12);
        $pdf->Cell(176, 5, utf8_decode('Adresse Abidjan - Cococdy , Contact : + 225 01 53 68 68 19'), 0, 0, 'C');
        // $pdf->Ln();

        $pdf->SetXY(15, 20);
        $pdf->SetFont('Times', '', 9);
        $pdf->Cell(176, 5, utf8_decode(' Email : contact@tarafe.com - Site web : www.tarafe.com - Facebook  / Twitter : @Tarafe'), 0, 0, 'C');
        // $pdf->Ln();

        $pdf->SetFont('', 'B', 12);
        $pdf->SetLineWidth(.5);
        $pdf->SetFillColor(81, 166, 245);
        $pdf->SetXY(168, 15);
        $pdf->Cell(2, 15, '', 'T', 1, 'L', true);

        $pdf->SetXY(170, 18);
        $pdf->SetFont('ARIAL', 'B', 11);
        $pdf->Cell(40, 5, utf8_decode('FACTURE'), 0, 0, 'L');

        $pdf->SetXY(170, 24);
        $pdf->SetFont('ARIAL', 'B', 11);

        foreach ($orders as $value):
            $pdf->Cell(40, 5, utf8_decode('N° ') . " " . $value->transaction_id, 0, 0, 'L');
        endforeach;


        $pdf->Ln(10);
        $pdf->SetX(35, 30);
        $status = "";
        $Etats = "";
        foreach ($ordersData as $valuex):

            $status = $valuex->status_orders;
            if ($status == 0) {
                $Etats = "est en attente de valider";
            } else if ($status == 0) {
                $Etats = "Commande validée";
            } else if ($status == 1) {
                $Etats = "Commande Annulée";
            } else if ($status == 3) {
                $Etats = "Commande En préparation";
            } else if ($status == 4) {
                $Etats = "Livraison en cours";
            } else if ($status == 5) {
                $Etats = "Livrée";
            }

            // $pdf->Cell(100, 20,utf8_decode('La commande n°8259 a été passée le 12/10/2022 et est actuellement à valider'), 0, 1, 'L', false);
            $pdf->Cell(100, 20, utf8_decode('La commande N°') . " " . $valuex->transaction_id . " " . utf8_decode('passée le ') . " " . date_format(date_create($valuex->date_orders), "d/m/y") . " " . utf8_decode($Etats), 0, 1, 'L', false);
        endforeach;

        $pdf->SetXY(30, 55);
        $pdf->SetFillColor(230, 230, 0);
        $pdf->SetLineWidth(.3);
        $pdf->SetFont('ARIAL', 'B', 10);

        $pdf->Cell(8, 8, utf8_decode('N°'), 1, 0, 'C');
        $pdf->Cell(70, 8, utf8_decode('Produits'), 1, 0, 'C');
        $pdf->Cell(37, 8, utf8_decode('Quantité'), 1, 0, 'C');
        $pdf->Cell(32, 8, utf8_decode('Prix'), 1, 0, 'C');

        $pdf->Ln();
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(96, 96, 96);

        $i = 1;
        foreach ($orders as $value):
            $pdf->SetX(30);
            $pdf->Cell(8, 7, $i, 1, 0, 'C');
            $pdf->Cell(70, 7, utf8_decode(substr($value->name_product, 0, 60)), 1, 0, 'L');
            $pdf->Cell(37, 7, $value->quantity, 1, 0, 'C');
            $pdf->Cell(32, 7, number_format(($value->price * $value->quantity), 0, ',', ' '), 1, 0, 'C');
            $pdf->Ln();
            $i++;
        endforeach;

        $pdf->Ln(15);
        $pdf->SetXY(30, 125);
        $pdf->SetFillColor(230, 230, 0);
        $pdf->SetLineWidth(.3);
        $pdf->SetFont('ARIAL', 'B', 10);

        $pdf->Cell(70, 8, utf8_decode('Moyen de paiement'), 1, 0, 'C');
        foreach ($ordersData as $values):
            $pdf->Cell(50, 8, utf8_decode($values->Mode_paiement), 1, 0, 'C');
        endforeach;
        $pdf->Cell(27, 8, utf8_decode('Sommes'), 1, 0, 'C');

        $pdf->Ln();
        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(96, 96, 96);

        foreach ($ordersData as $valuex):
            $pdf->SetX(100);
            $pdf->Cell(50, 7, 'Sous Total :  ', 1, 0, 'C');
            $pdf->Cell(27, 7, number_format($valuex->total, 0, ',', ' '), 1, 0, 'C');

            $pdf->Ln();
            $pdf->SetX(100);
            $pdf->Cell(50, 7, 'Total :  ', 1, 0, 'C');
            $pdf->Cell(27, 7, number_format($valuex->total, 0, ',', ' '), 1, 0, 'C');
        endforeach;

        $pdf->Output();
        exit;
    }
}
