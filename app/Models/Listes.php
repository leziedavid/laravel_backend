<?php

namespace App\Models;

use NumberFormatter;
use Codedge\Fpdf\Fpdf\Fpdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PDFx extends Fpdf
{

   function Footer()
   {
      // Position at 1.5 cm from bottom
      $this->SetY(-15);
      // Arial italic 8
      $this->SetFont('Arial', 'I', 8);
      // Page number
      $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
   }
}

class Listes extends Model
{
   use HasFactory;

   public static function ListeUsers()
   {
      $pdf = new Fpdf;

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
      $pdf->Cell(176, 5, utf8_decode('Email : contact@tarafe.com - Site web : www.tarafe.com - Facebook  / Twitter : @Tarafe'), 0, 0, 'C');
      // $pdf->Ln();

      $pdf->SetFont('', 'B', 12);
      $pdf->SetLineWidth(.5);
      $pdf->SetFillColor(81, 166, 245);
      $pdf->SetXY(168, 15);
      $pdf->Cell(2, 15, '', 'T', 1, 'L', true);

      $pdf->SetXY(170, 18);
      $pdf->SetFont('ARIAL', 'B', 11);
      $pdf->Cell(40, 5, utf8_decode('2022-2023'), 0, 0, 'L');

      $users = DB::table('users')
         ->where('users.status', '=', 1)
         ->orderByRaw('name ASC')
         ->distinct()
         ->get();



      $pdf->SetXY(4, 52);
      $pdf->SetFont('', 'B', 12);
      $pdf->SetLineWidth(.5);
      $pdf->Cell(202, 6, 'LISTE DES UTILISATEUR', 'TB', 1, 'L', false);
      $pdf->Ln(2);
      //   $pdf->Ln(2);

      $pdf->SetFillColor(230, 230, 0);
      $pdf->SetLineWidth(.3);
      $pdf->SetFont('ARIAL', 'B', 10);
      $pdf->Cell(10, 5, utf8_decode('N°'), 1, 0, 'C');
      $pdf->Cell(70, 5, 'NOM', 1, 0, 'C');
      $pdf->Cell(70, 5, 'EMAIL', 1, 0, 'C');
      $pdf->Cell(30, 5, 'CONTACT', 1, 0, 'C');
      $pdf->Cell(25, 5, 'QUARTIER', 1, 0, 'C');
      $pdf->Ln();
      $starter = 1;

      foreach ($users as $value):

         $pdf->Cell(10, 5, utf8_decode($starter), 1, 0, 'C');
         $pdf->Cell(70, 5, utf8_decode($value->name), 1, 0, 'L');
         $pdf->Cell(70, 5, utf8_decode($value->email), 1, 0, 'L');
         $pdf->Cell(30, 5, utf8_decode($value->contact), 1, 0, 'L');
         $pdf->Cell(25, 5, utf8_decode($value->quartier), 1, 0, 'L');
         $pdf->Ln();

         $starter++;
      endforeach;

      $pdf->Output();
      exit;
   }

   public static function pdfpaiement($eleve, $codeEtab, $sessionEtab, $versementid, $classeid)
   {

      $versementStudent = DB::table('versement')
         ->join('users', 'users.id', '=', 'versement.ideleve_versement')
         ->join('inscription', 'inscription.ideleve_inscrip', '=', 'versement.ideleve_versement')
         ->join('classe', 'classe.id_classe', '=', 'versement.classe_versement')
         ->join('etablissement', 'etablissement.code_etab', '=', 'versement.codeEtab_versement')
         ->where('versement.id_versement', $versementid)
         ->where('versement.ideleve_versement', '=', $eleve)
         ->where('versement.codeEtab_versement', '=', $codeEtab)
         ->where('versement.session_versement', $sessionEtab)
         ->where('inscription.session_inscrip', $sessionEtab)
         ->where('versement.classe_versement', $classeid)
         ->get();

      foreach ($versementStudent as  $valueVersement):
         $deposant = $valueVersement->deposant_versement;
         $motif = $valueVersement->motif_versement;
         $mode = $valueVersement->mode_versement;
         $montant = $valueVersement->montant_versement;
         $solde = $valueVersement->solde_versement;
         $code = $valueVersement->code_versement;
         $dateversement = $valueVersement->date_versement;
         $beneficiaire = $valueVersement->nom_users . " " . $valueVersement->prenom_users;
         $beneficiairematri = $valueVersement->matricule_users;
         $beneficiaireclasse = $valueVersement->libelle_classe;
         $scolaritesmontant = $valueVersement->scolarite_classe;
         $scolaritesnet = $montant + $solde;
         $motifid = $valueVersement->motifid_versement;
         $dupaiement = $valueVersement->du_versement;
         $libelleactivities = "";
         $libelleclasse = $valueVersement->libelle_classe;
         $lemotiflibe = "";

         $today = date("d/m/Y");

         // if($motif=="AES")
         // {
         //   $datasactivities=$etabs->getActivitiesDetails($motifid,$codeEtab,$session);
         //   foreach ($datasactivities as $valueactivities):
         //     $libelleactivities=$valueactivities->libelle_act;
         //   endforeach;
         // }


         $libellemode = "";

         if ($mode == 1) {
            $libellemode = "CHEQUES";
         } else if ($mode == 2) {
            $libellemode = "ESPECES";
         } else if ($mode == 3) {
            $libellemode = "TPE";
         } else if ($mode == 4) {
            $libellemode = "Virement Bancaire";
         }


         // dd($datas[0]->id);

         $pdf = new Fpdf;
         $pdf->AddPage('L', 'A4');
         $pdf->SetTitle('RECU DE PAIEMENT ');

         $pdf->SetAutoPageBreak(true, 10);
         $pdf->SetFont('Arial', '', 12);
         $pdf->SetTopMargin(10);
         $pdf->SetLeftMargin(4);
         $pdf->SetRightMargin(50);
         $pdf->AliasNbPages();
         $pdf->Ln(15);
         $pdf->SetX(2);
         $filelogo = public_path('images/logos2.png');
         $pdf->Image($filelogo, 10, 5, 35, 35);


         /* --- Cell --- */
         $pdf->SetTextColor(65, 83, 162);
         $pdf->SetXY(54, 7);
         $pdf->SetFont('Times', 'B', 14);
         $pdf->Cell(180, 13, 'INTERNATIONAL BILINGUAL SCHOOLS OF AFRICA', 'TB', 1, 'C', false);
         $pdf->SetTextColor(0);
         /* --- Cell --- */
         $pdf->SetXY(175, 20);
         $pdf->SetFont('Arial', 'B', 12);
         $pdf->Cell(47, 10, "DATE : " . $today, 0, 1, 'L', false);

         if ($motif == "SCOLARITES" || $motif == "INSCRIPTIONS" || $motif == "REINSCRIPTIONS") {
            if ($motif == "SCOLARITES") {
               $lemotiflibe = "SCOLARITE";
            } else if ($motif == "SCOLARITES") {
               $lemotiflibe = "INSCRIPTION";
            } else if ($motif == "REINSCRIPTIONS") {
               $lemotiflibe = "REINSCRIPTION";
            }

            if ($mode == 3 || $mode == 4 || $mode == 1) {

               $pdf->SetXY(53, 20);
               $pdf->SetFontSize(17);
               $pdf->SetTextColor(78, 183, 72);
               $pdf->Cell(90, 12, utf8_decode("RECU - " . $lemotiflibe), 0, 1, 'L', false);

               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(12, 68);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(40, -39, utf8_decode('NOM ET PRENOMS '), 0, 0, 'C');
               $pdf->SetFont('', 'B', 12);
               $tabbeneficiaire = explode(" ", $beneficiaire);
               $nbtabbenef = count($tabbeneficiaire);

               $pdf->SetX("55");
               $pdf->SetFont('Arial', '', 13);
               $pdf->Cell(93, -39, ":" . " " . utf8_decode(ucwords(strtoupper($beneficiaire))), 0, 0, 'L');

               $pdf->SetXY(1, 74);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(40, -39, utf8_decode('CLASSE '), 0, 0, 'C');

               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(57, 74);
               $pdf->SetFont('Arial', '', 12);
               $pdf->Cell(40, -39, utf8_decode(': ' . $libelleclasse), 0, 0, 'C');

               $pdf->SetXY(120, 30);
               $pdf->SetFontSize(14);
               $pdf->SetTextColor(0, 0, 0);
               $pdf->Cell(55, 9, number_format($montant, 0, ',', ' ') . ' ' . 'FCFA', 1, 1, 'C', false);

               $pdf->SetXY(62, 32);
               $pdf->SetFont('Arial', 'B', 13);
               $pdf->Cell(0, 5, 'Montant:', 0, 1, 'L', false);
               $pdf->SetXY(180, 35);
               $pdf->SetFontSize(14);
               $pdf->SetTextColor(255, 0, 0);
               $tabcode = explode("VER", $code);
               $versementcode = utf8_decode("N° " . $tabcode[1]);
               $pdf->SetFont('Arial', 'I', 12);
               $pdf->Cell(0, 0, $versementcode, 0, 0, 'C');
               $pdf->SetXY(61, 68);
               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(13, 80);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(40, -37, utf8_decode('MONTANT(en lettres)'), 0, 0, 'C');
               $pdf->SetFont('Arial', '', 12);
               $pdf->SetX("55");
               $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
               $pdf->Cell(10, -37, ":" . " " . utf8_decode(ucfirst($f->format($montant)) . ' Francs CFA'), 0, 0, 'L');

               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(11, 85);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(48, -33, utf8_decode('RESTE A PAYER'), 0, 0, 'L');
               $pdf->SetX("55");
               $pdf->SetFont('Arial', '', 12);
               if ($solde == 0) {
                  $pdf->Cell(145, -33, ":" . " " . utf8_decode(ucwords($solde) . ' ' . 'FCFA'), 0, 0, 'L');
               } else {
                  $pdf->Cell(145, -33, ":" . " " . utf8_decode(ucfirst($f->format($solde)) . ' ' . 'FCFA'), 0, 0, 'L');
               }
               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(11, 91);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(24, -31, utf8_decode(strtoupper('Mode de paiement')), 0, 0, 'L');
               $pdf->SetFont('Arial', '', 14);
               $pdf->SetX("55");
               $pdf->Cell(0, -31, ":" . " " . ucfirst(utf8_decode($libellemode)), 0, 0, 'L');
               $pdf->SetXY(11, 78);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(38, 8, utf8_decode(strtoupper('Du')), 0, 0, 'L', false);

               $pdf->SetXY(55, 78);
               $pdf->SetFont('Arial', '', 13);
               $pdf->Cell(135, 8, ':' . utf8_decode($dupaiement), 0, 0, 'L', false);


               $pdf->SetXY(2, 109);
               $pdf->Cell(290, 0, '', 1, 1, 'L', false);
               $pdf->SetFont('Arial', 'BU', 11);

               $pdf->SetXY(8, 107);
               $pdf->Cell(80, -36, 'REMETTANT', 0, 0, 'C');
               $pdf->SetXY(170, 107);
               $pdf->Cell(110, -36, utf8_decode("SERVICE COMPTABILITE"), 0, 0, 'C');

               $pdf->Image($filelogo, 10, 116, 35, 35);

               $pdf->SetTextColor(65, 83, 162);
               $pdf->SetXY(54, 116);
               $pdf->SetFont('Times', 'B', 14);
               $pdf->Cell(180, 13, 'INTERNATIONAL BILINGUAL SCHOOLS OF AFRICA', 'TB', 1, 'C', false);
               $pdf->SetTextColor(0);

               $pdf->SetXY(175, 129);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(47, 10, "DATE : " . $today, 0, 1, 'L', false);

               $pdf->SetXY(53, 129);
               $pdf->SetFontSize(17);
               $pdf->SetTextColor(78, 183, 72);
               $pdf->Cell(90, 12, utf8_decode("RECU - " . $lemotiflibe), 0, 1, 'L', false);

               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(12, 177);
               $pdf->SetFont('Arial', 'B', 11);
               $pdf->Cell(40, -39, utf8_decode('NOM ET PRENOMS '), 0, 0, 'C');
               $pdf->SetFont('', 'B', 12);
               $tabbeneficiaire = explode(" ", $beneficiaire);
               $nbtabbenef = count($tabbeneficiaire);

               $pdf->SetX("55");
               $pdf->SetFont('Arial', '', 13);
               $pdf->Cell(93, -39, ":" . " " . utf8_decode(ucwords(strtoupper($beneficiaire))), 0, 0, 'L');

               $pdf->SetXY(1, 182);
               $pdf->SetFont('Arial', 'B', 11);
               $pdf->Cell(40, -39, utf8_decode('CLASSE '), 0, 0, 'C');

               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(57, 182);
               $pdf->SetFont('Arial', '', 11);
               $pdf->Cell(40, -39, utf8_decode(': ' . $libelleclasse), 0, 0, 'C');

               $pdf->SetXY(120, 140);
               $pdf->SetFontSize(14);
               $pdf->SetTextColor(0, 0, 0);
               $pdf->Cell(55, 9, number_format($montant, 0, ',', ' ') . ' ' . 'FCFA', 1, 1, 'C', false);
               $pdf->SetXY(62, 142);
               $pdf->SetFont('Arial', 'B', 13);
               $pdf->Cell(0, 5, 'Montant:', 0, 1, 'L', false);
               $pdf->SetXY(180, 142);
               $pdf->SetFontSize(14);
               $pdf->SetTextColor(255, 0, 0);
               $tabcode = explode("VER", $code);
               $versementcode = utf8_decode("N° " . $tabcode[1]);
               $pdf->SetFont('Arial', 'I', 12);
               $pdf->Cell(0, 0, $versementcode, 0, 0, 'C');
               $pdf->SetXY(61, 68);

               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(12, 186);
               $pdf->SetFont('Arial', 'B', 11);
               $pdf->Cell(40, -37, utf8_decode('MONTANT(en lettres)'), 0, 0, 'C');
               $pdf->SetFont('Arial', '', 11);
               $pdf->SetX("55");
               $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
               $pdf->Cell(10, -37, ":" . " " . utf8_decode(ucfirst($f->format($montant)) . ' Francs CFA'), 0, 0, 'L');

               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(12, 190);
               $pdf->SetFont('Arial', 'B', 11);
               $pdf->Cell(48, -33, utf8_decode('RESTE A PAYER'), 0, 0, 'L');
               $pdf->SetX("55");
               $pdf->SetFont('Arial', '', 11);
               if ($solde == 0) {
                  $pdf->Cell(145, -33, ":" . " " . utf8_decode(ucwords($solde) . ' ' . 'FCFA'), 0, 0, 'L');
               } else {
                  $pdf->Cell(145, -33, ":" . " " . utf8_decode(ucfirst($f->format($solde)) . ' ' . 'FCFA'), 0, 0, 'L');
               }

               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(12, 195);
               $pdf->SetFont('Arial', 'B', 11);
               $pdf->Cell(24, -31, utf8_decode(strtoupper('Mode de paiement')), 0, 0, 'L');
               $pdf->SetFont('Arial', '', 14);
               $pdf->SetX("55");
               $pdf->Cell(0, -31, ":" . " " . ucfirst(utf8_decode($libellemode)), 0, 0, 'L');

               $pdf->SetXY(12, 182);
               $pdf->SetFont('Arial', 'B', 11);
               $pdf->Cell(38, 8, utf8_decode(strtoupper('Du')), 0, 0, 'L', false);

               $pdf->SetXY(55, 182);
               $pdf->SetFont('Arial', '', 11);
               $pdf->Cell(135, 8, ':' . utf8_decode($dupaiement), 0, 0, 'L', false);


               $pdf->SetFont('Arial', 'BU', 11);
               $pdf->SetXY(8, 210);
               $pdf->Cell(80, -36, 'REMETTANT', 0, 0, 'C');
               $pdf->SetXY(170, 210);
               $pdf->Cell(110, -36, utf8_decode("SERVICE COMPTABILITE"), 0, 0, 'C');
            } else {

               $pdf->SetXY(53, 20);
               $pdf->SetFontSize(17);
               $pdf->SetTextColor(78, 183, 72);
               $pdf->Cell(90, 12, utf8_decode("RECU - " . $lemotiflibe), 0, 1, 'L', false);

               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(12, 68);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(40, -39, utf8_decode('NOM ET PRENOMS '), 0, 0, 'C');
               $pdf->SetFont('', 'B', 12);
               $tabbeneficiaire = explode(" ", $beneficiaire);
               $nbtabbenef = count($tabbeneficiaire);

               $pdf->SetX("55");
               $pdf->SetFont('Arial', '', 13);
               $pdf->Cell(93, -39, ":" . " " . utf8_decode(ucwords(strtoupper($beneficiaire))), 0, 0, 'L');

               $pdf->SetXY(1, 74);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(40, -39, utf8_decode('CLASSE '), 0, 0, 'C');

               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(57, 74);
               $pdf->SetFont('Arial', '', 12);
               $pdf->Cell(40, -39, utf8_decode(': ' . $libelleclasse), 0, 0, 'C');

               $pdf->SetXY(120, 30);
               $pdf->SetFontSize(14);
               $pdf->SetTextColor(0, 0, 0);
               $pdf->Cell(55, 9, number_format($montant, 0, ',', ' ') . ' ' . 'FCFA', 1, 1, 'C', false);

               $pdf->SetXY(62, 32);
               $pdf->SetFont('Arial', 'B', 13);
               $pdf->Cell(0, 5, 'Montant:', 0, 1, 'L', false);
               $pdf->SetXY(180, 35);
               $pdf->SetFontSize(14);
               $pdf->SetTextColor(255, 0, 0);
               $tabcode = explode("VER", $code);
               $versementcode = utf8_decode("N° " . $tabcode[1]);
               $pdf->SetFont('Arial', 'I', 12);
               $pdf->Cell(0, 0, $versementcode, 0, 0, 'C');
               $pdf->SetXY(61, 68);


               // $pdf->Ln(10);
               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(13, 80);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(40, -37, utf8_decode('MONTANT(en lettres)'), 0, 0, 'C');
               $pdf->SetFont('Arial', '', 12);
               $pdf->SetX("55");
               $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
               $pdf->Cell(10, -37, ":" . " " . utf8_decode(ucfirst($f->format($montant)) . ' Francs CFA'), 0, 0, 'L');

               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(11, 85);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(48, -33, utf8_decode('RESTE A PAYER'), 0, 0, 'L');
               $pdf->SetX("55");
               $pdf->SetFont('Arial', '', 12);
               if ($solde == 0) {
                  $pdf->Cell(145, -33, ":" . " " . utf8_decode(ucwords($solde) . ' ' . 'FCFA'), 0, 0, 'L');
               } else {
                  $pdf->Cell(145, -33, ":" . " " . utf8_decode(ucfirst($f->format($solde)) . ' ' . 'FCFA'), 0, 0, 'L');
               }
               $pdf->SetTextColor(10, 5, 8);
               $pdf->SetXY(11, 91);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(24, -31, utf8_decode(strtoupper('Mode de paiement')), 0, 0, 'L');
               $pdf->SetFont('Arial', '', 14);
               $pdf->SetX("55");
               $pdf->Cell(0, -31, ":" . " " . ucfirst(utf8_decode($libellemode)), 0, 0, 'L');

               $pdf->SetXY(2, 109);
               $pdf->Cell(290, 0, '', 1, 1, 'L', false);
               $pdf->SetFont('Arial', 'BU', 11);

               $pdf->SetXY(8, 102);
               $pdf->Cell(80, -36, 'REMETTANT', 0, 0, 'C');
               $pdf->SetXY(170, 102);
               $pdf->Cell(110, -36, utf8_decode("SERVICE COMPTABILITE"), 0, 0, 'C');

               $pdf->Image($filelogo, 10, 116, 35, 35);

               $pdf->SetTextColor(65, 83, 162);
               $pdf->SetXY(54, 116);
               $pdf->SetFont('Times', 'B', 14);
               $pdf->Cell(180, 13, 'INTERNATIONAL BILINGUAL SCHOOLS OF AFRICA', 'TB', 1, 'C', false);
               $pdf->SetTextColor(0);

               $pdf->SetXY(175, 129);
               $pdf->SetFont('Arial', 'B', 12);
               $pdf->Cell(47, 10, "DATE : " . $today, 0, 1, 'L', false);

               if ($motif == "SCOLARITES" || $motif == "INSCRIPTIONS" || $motif == "REINSCRIPTIONS") {

                  if ($mode == 3 || $mode == 4 || $mode == 1) {
                  } else {
                     $pdf->SetXY(53, 129);
                     $pdf->SetFontSize(17);
                     $pdf->SetTextColor(78, 183, 72);
                     $pdf->Cell(90, 12, utf8_decode("RECU - " . $lemotiflibe), 0, 1, 'L', false);

                     $pdf->SetTextColor(10, 5, 8);
                     $pdf->SetXY(12, 177);
                     $pdf->SetFont('Arial', 'B', 12);
                     $pdf->Cell(40, -39, utf8_decode('NOM ET PRENOMS '), 0, 0, 'C');
                     $pdf->SetFont('', 'B', 12);
                     $tabbeneficiaire = explode(" ", $beneficiaire);
                     $nbtabbenef = count($tabbeneficiaire);

                     $pdf->SetX("55");
                     $pdf->SetFont('Arial', '', 13);
                     $pdf->Cell(93, -39, ":" . " " . utf8_decode(ucwords(strtoupper($beneficiaire))), 0, 0, 'L');

                     $pdf->SetXY(1, 183);
                     $pdf->SetFont('Arial', 'B', 12);
                     $pdf->Cell(40, -39, utf8_decode('CLASSE '), 0, 0, 'C');

                     $pdf->SetTextColor(10, 5, 8);
                     $pdf->SetXY(57, 183);
                     $pdf->SetFont('Arial', '', 12);
                     $pdf->Cell(40, -39, utf8_decode(': ' . $libelleclasse), 0, 0, 'C');

                     $pdf->SetXY(120, 140);
                     $pdf->SetFontSize(14);
                     $pdf->SetTextColor(0, 0, 0);
                     $pdf->Cell(55, 9, number_format($montant, 0, ',', ' ') . ' ' . 'FCFA', 1, 1, 'C', false);


                     $pdf->SetXY(62, 142);
                     $pdf->SetFont('Arial', 'B', 13);
                     $pdf->Cell(0, 5, 'Montant:', 0, 1, 'L', false);
                     $pdf->SetXY(180, 35);
                     $pdf->SetFontSize(14);
                     $pdf->SetTextColor(255, 0, 0);
                     $tabcode = explode("VER", $code);
                     $versementcode = utf8_decode("N° " . $tabcode[1]);
                     $pdf->SetFont('Arial', 'I', 12);
                     $pdf->Cell(0, 0, $versementcode, 0, 0, 'C');
                     $pdf->SetXY(61, 68);

                     $pdf->SetTextColor(10, 5, 8);
                     $pdf->SetXY(13, 188);
                     $pdf->SetFont('Arial', 'B', 12);
                     $pdf->Cell(40, -37, utf8_decode('MONTANT(en lettres)'), 0, 0, 'C');
                     $pdf->SetFont('Arial', '', 12);
                     $pdf->SetX("55");
                     $f = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
                     $pdf->Cell(10, -37, ":" . " " . utf8_decode(ucfirst($f->format($montant)) . ' Francs CFA'), 0, 0, 'L');

                     $pdf->SetTextColor(10, 5, 8);
                     $pdf->SetXY(11, 193);
                     $pdf->SetFont('Arial', 'B', 12);
                     $pdf->Cell(48, -33, utf8_decode('RESTE A PAYER'), 0, 0, 'L');
                     $pdf->SetX("55");
                     $pdf->SetFont('Arial', '', 12);
                     if ($solde == 0) {
                        $pdf->Cell(145, -33, ":" . " " . utf8_decode(ucwords($solde) . ' ' . 'FCFA'), 0, 0, 'L');
                     } else {
                        $pdf->Cell(145, -33, ":" . " " . utf8_decode(ucfirst($f->format($solde)) . ' ' . 'FCFA'), 0, 0, 'L');
                     }

                     $pdf->SetTextColor(10, 5, 8);
                     $pdf->SetXY(11, 199);
                     $pdf->SetFont('Arial', 'B', 12);
                     $pdf->Cell(24, -31, utf8_decode(strtoupper('Mode de paiement')), 0, 0, 'L');
                     $pdf->SetFont('Arial', '', 14);
                     $pdf->SetX("55");
                     $pdf->Cell(0, -31, ":" . " " . ucfirst(utf8_decode($libellemode)), 0, 0, 'L');

                     $pdf->SetFont('Arial', 'BU', 11);
                     $pdf->SetXY(8, 210);
                     $pdf->Cell(80, -36, 'REMETTANT', 0, 0, 'C');
                     $pdf->SetXY(170, 210);
                     $pdf->Cell(110, -36, utf8_decode("SERVICE COMPTABILITE"), 0, 0, 'C');
                  }
               }
            }
         }


      endforeach;

      $pdf->Output();
      exit;
   }

   public static function pdflistecantineshistorique($codeEtab, $sessionEtab)
   {
      $pdf = new Fpdf;
      $pdf->AddPage('L', 'A4');
      $pdf->SetTitle('LISTE DES PAIEMENTS CANTINE');
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetFont('Arial', '', 12);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(4);
      $pdf->SetRightMargin(50);
      $pdf->AliasNbPages();
      $pdf->Ln(10);
      $pdf->SetX(2);
      $filelogo = public_path('logo/sossco.png');
      $pdf->Image($filelogo, 20, 10, 30, 30);

      $pdf->SetXY(60, 7);
      $pdf->SetFont('Times', 'B', 14);
      $pdf->Cell(150, 13, 'RECAPITULATIF DES PAIEMENTS CANTINE', 'TB', 1, 'C', false);
      // $pdf->SetTextColor(0);

      $pdf->SetXY(215, 22);
      $pdf->SetFont('Arial', 'B', 10);
      // $pdf->SetTextColor(255,255,255);
      $pdf->Cell(0, 3, 'ANNEE SCOLAIRE: ' . $sessionEtab, 0, 1, 'L', false);

      $pdf->Ln(15);
      $pdf->SetX(15);
      $pdf->SetFillColor(230, 230, 0);
      $pdf->SetLineWidth(.3);
      $pdf->SetFont('ARIAL', 'B', 10);
      $pdf->Cell(8, 8, utf8_decode('N°'), 1, 0, 'C');
      $pdf->Cell(20, 8, utf8_decode('Date'), 1, 0, 'C');
      $pdf->Cell(70, 8, utf8_decode('Bénéficiaires'), 1, 0, 'C');
      $pdf->Cell(27, 8, utf8_decode('Classes'), 1, 0, 'C');
      $pdf->Cell(37, 8, utf8_decode('Forfaits'), 1, 0, 'C');
      $pdf->Cell(32, 8, utf8_decode('Montant dû '), 1, 0, 'C');
      $pdf->Cell(37, 8, utf8_decode('Montant versé '), 1, 0, 'C');

      $pdf->Cell(32, 8, utf8_decode('Solde'), 1, 0, 'C');

      $pdf->Ln();
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetFillColor(96, 96, 96);


      $versements = DB::table('versement')
         ->join('users', 'users.id', '=', 'versement.ideleve_versement')
         ->join('classe', 'classe.id_classe', '=', 'versement.classe_versement')
         ->where('codeEtab_versement', '=', $codeEtab)
         ->where('session_versement', '=', $sessionEtab)
         ->where('motif_versement', '=', 'CANTINES')
         ->orderByRaw('versement.id_versement DESC')
         ->get();
      $i = 1;
      foreach ($versements as $value):
         $pdf->SetX(15);
         $pdf->Cell(8, 7, $i, 1, 0, 'C');
         $pdf->Cell(20, 7, date_format(date_create($value->date_versement), "d/m/y"), 1, 0, 'C');
         $pdf->Cell(70, 7, substr($value->nom_users . ' ' . $value->prenom_users, 0, 20), 1, 0, 'L');
         $libelleclasse = $value->libelle_classe;
         $tabclasse = explode("/", $libelleclasse);
         $pdf->Cell(27, 7, $tabclasse[0], 1, 0, 'C');
         $pdf->Cell(37, 7, $value->trajet_versement, 1, 0, 'C');

         $pdf->Cell(32, 7, number_format($value->montantapayer_versement, 0, ',', ' '), 1, 0, 'C');
         $pdf->Cell(37, 7, number_format(($value->montant_versement), 0, ',', ' '), 1, 0, 'C');

         $pdf->Cell(32, 7, number_format($value->solde_versement, 0, ',', ' '), 1, 0, 'C');
         $pdf->Ln();
         $i++;

      endforeach;



      $pdf->Output();
      exit;
   }

   public static function pdflistecantineshistoriquestudent($studentid, $codeEtab, $sessionEtab)
   {

      $pdf = new Fpdf;
      $pdf->AddPage('L', 'A4');
      $pdf->SetTitle('LISTE DES PAIEMENTS CANTINE');
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetFont('Arial', '', 12);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(4);
      $pdf->SetRightMargin(50);
      $pdf->AliasNbPages();
      $pdf->Ln(10);
      $pdf->SetX(2);
      $filelogo = public_path('logo/sossco.png');
      $pdf->Image($filelogo, 20, 10, 30, 30);

      $pdf->SetXY(60, 7);
      $pdf->SetFont('Times', 'B', 14);
      $pdf->Cell(150, 13, 'RECAPITULATIF DES PAIEMENTS CANTINE', 'TB', 1, 'C', false);
      // $pdf->SetTextColor(0);

      $pdf->SetXY(215, 22);
      $pdf->SetFont('Arial', 'B', 10);
      // $pdf->SetTextColor(255,255,255);
      $pdf->Cell(0, 3, 'ANNEE SCOLAIRE: ' . $sessionEtab, 0, 1, 'L', false);

      $pdf->Ln(15);
      $pdf->SetX(15);
      $pdf->SetFillColor(230, 230, 0);
      $pdf->SetLineWidth(.3);
      $pdf->SetFont('ARIAL', 'B', 10);
      $pdf->Cell(8, 8, utf8_decode('N°'), 1, 0, 'C');
      $pdf->Cell(20, 8, utf8_decode('Date'), 1, 0, 'C');
      $pdf->Cell(70, 8, utf8_decode('Bénéficiaires'), 1, 0, 'C');
      $pdf->Cell(27, 8, utf8_decode('Classes'), 1, 0, 'C');
      $pdf->Cell(37, 8, utf8_decode('Forfaits'), 1, 0, 'C');
      $pdf->Cell(32, 8, utf8_decode('Montant dû '), 1, 0, 'C');
      $pdf->Cell(37, 8, utf8_decode('Montant versé '), 1, 0, 'C');

      $pdf->Cell(32, 8, utf8_decode('Solde'), 1, 0, 'C');

      $pdf->Ln();
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetFillColor(96, 96, 96);



      $versements = DB::table('versement')
         ->join('users', 'users.id', '=', 'versement.ideleve_versement')
         ->join('classe', 'classe.id_classe', '=', 'versement.classe_versement')
         ->where('users.id', '=', $studentid)
         ->where('codeEtab_versement', '=', $codeEtab)
         ->where('session_versement', '=', $sessionEtab)
         ->where('motif_versement', '=', 'CANTINES')
         ->orderByRaw('versement.id_versement DESC')
         ->get();
      $i = 1;
      foreach ($versements as $value):
         $pdf->SetX(15);
         $pdf->Cell(8, 7, $i, 1, 0, 'C');
         $pdf->Cell(20, 7, date_format(date_create($value->date_versement), "d/m/y"), 1, 0, 'C');
         $pdf->Cell(70, 7, substr($value->nom_users . ' ' . $value->prenom_users, 0, 20), 1, 0, 'L');
         $libelleclasse = $value->libelle_classe;
         $tabclasse = explode("/", $libelleclasse);
         $pdf->Cell(27, 7, $tabclasse[0], 1, 0, 'C');
         $pdf->Cell(37, 7, $value->trajet_versement, 1, 0, 'C');

         $pdf->Cell(32, 7, number_format($value->montantapayer_versement, 0, ',', ' '), 1, 0, 'C');
         $pdf->Cell(37, 7, number_format(($value->montant_versement), 0, ',', ' '), 1, 0, 'C');

         $pdf->Cell(32, 7, number_format($value->solde_versement, 0, ',', ' '), 1, 0, 'C');
         $pdf->Ln();
         $i++;

      endforeach;



      $pdf->Output();
      exit;
   }

   public static function pdflistecantineshistoriquestudentperiode($studentid, $datedebut, $datedefin, $codeEtab, $sessionEtab)
   {


      $pdf = new Fpdf;
      $pdf->AddPage('L', 'A4');
      $pdf->SetTitle('LISTE DES PAIEMENTS CANTINE');
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetFont('Arial', '', 12);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(4);
      $pdf->SetRightMargin(50);
      $pdf->AliasNbPages();
      $pdf->Ln(10);
      $pdf->SetX(2);
      $filelogo = public_path('logo/sossco.png');
      $pdf->Image($filelogo, 20, 10, 30, 30);

      $pdf->SetXY(60, 7);
      $pdf->SetFont('Times', 'B', 14);
      $pdf->Cell(150, 13, 'RECAPITULATIF DES PAIEMENTS CANTINE', 'TB', 1, 'C', false);
      // $pdf->SetTextColor(0);

      $pdf->SetXY(215, 22);
      $pdf->SetFont('Arial', 'B', 10);
      // $pdf->SetTextColor(255,255,255);
      $pdf->Cell(0, 3, 'ANNEE SCOLAIRE: ' . $sessionEtab, 0, 1, 'L', false);

      $pdf->SetXY(80, 32);
      $pdf->SetFont('Arial', 'B', 14);
      // $pdf->SetTextColor(255,255,255);
      $pdf->Cell(0, 3, utf8_decode("Période du ") . date_format(date_create($datedebut), "d-m-Y") . " au " . date_format(date_create($datedefin), "d-m-Y"), 0, 1, 'L', false);


      $pdf->Ln(18);
      $pdf->SetX(15);
      $pdf->SetFillColor(230, 230, 0);
      $pdf->SetLineWidth(.3);
      $pdf->SetFont('ARIAL', 'B', 10);
      $pdf->Cell(8, 8, utf8_decode('N°'), 1, 0, 'C');
      $pdf->Cell(20, 8, utf8_decode('Date'), 1, 0, 'C');
      $pdf->Cell(70, 8, utf8_decode('Bénéficiaires'), 1, 0, 'C');
      $pdf->Cell(27, 8, utf8_decode('Classes'), 1, 0, 'C');
      $pdf->Cell(37, 8, utf8_decode('Forfaits'), 1, 0, 'C');
      $pdf->Cell(32, 8, utf8_decode('Montant dû '), 1, 0, 'C');
      $pdf->Cell(37, 8, utf8_decode('Montant versé '), 1, 0, 'C');

      $pdf->Cell(32, 8, utf8_decode('Solde'), 1, 0, 'C');

      $pdf->Ln();
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetFillColor(96, 96, 96);



      $versements = DB::table('versement')
         ->join('users', 'users.id', '=', 'versement.ideleve_versement')
         ->join('classe', 'classe.id_classe', '=', 'versement.classe_versement')
         ->where('users.id', '=', $studentid)
         ->where('codeEtab_versement', '=', $codeEtab)
         ->where('session_versement', '=', $sessionEtab)
         ->where('motif_versement', '=', 'CANTINES')
         ->where('date_versement', '>=', $datedebut)
         ->where('date_versement', '<', $datedefin)
         ->orderByRaw('versement.id_versement DESC')
         ->get();
      $i = 1;
      foreach ($versements as $value):
         $pdf->SetX(15);
         $pdf->Cell(8, 7, $i, 1, 0, 'C');
         $pdf->Cell(20, 7, date_format(date_create($value->date_versement), "d/m/y"), 1, 0, 'C');
         $pdf->Cell(70, 7, substr($value->nom_users . ' ' . $value->prenom_users, 0, 20), 1, 0, 'L');
         $libelleclasse = $value->libelle_classe;
         $tabclasse = explode("/", $libelleclasse);
         $pdf->Cell(27, 7, $tabclasse[0], 1, 0, 'C');
         $pdf->Cell(37, 7, $value->trajet_versement, 1, 0, 'C');

         $pdf->Cell(32, 7, number_format($value->montantapayer_versement, 0, ',', ' '), 1, 0, 'C');
         $pdf->Cell(37, 7, number_format(($value->montant_versement), 0, ',', ' '), 1, 0, 'C');

         $pdf->Cell(32, 7, number_format($value->solde_versement, 0, ',', ' '), 1, 0, 'C');
         $pdf->Ln();
         $i++;

      endforeach;



      $pdf->Output();
      exit;
   }

   public static function pdflistecantineshistoriqueperiode($datedebut, $datedefin, $codeEtab, $sessionEtab)
   {



      $pdf = new Fpdf;
      $pdf->AddPage('L', 'A4');
      $pdf->SetTitle('LISTE DES PAIEMENTS CANTINE');
      $pdf->SetAutoPageBreak(true, 10);
      $pdf->SetFont('Arial', '', 12);
      $pdf->SetTopMargin(10);
      $pdf->SetLeftMargin(4);
      $pdf->SetRightMargin(50);
      $pdf->AliasNbPages();
      $pdf->Ln(10);
      $pdf->SetX(2);
      $filelogo = public_path('logo/sossco.png');
      $pdf->Image($filelogo, 20, 10, 30, 30);

      $pdf->SetXY(60, 7);
      $pdf->SetFont('Times', 'B', 14);
      $pdf->Cell(150, 13, 'RECAPITULATIF DES PAIEMENTS CANTINE', 'TB', 1, 'C', false);
      // $pdf->SetTextColor(0);

      $pdf->SetXY(215, 22);
      $pdf->SetFont('Arial', 'B', 10);
      // $pdf->SetTextColor(255,255,255);
      $pdf->Cell(0, 3, 'ANNEE SCOLAIRE: ' . $sessionEtab, 0, 1, 'L', false);

      $pdf->SetXY(80, 32);
      $pdf->SetFont('Arial', 'B', 14);
      // $pdf->SetTextColor(255,255,255);
      $pdf->Cell(0, 3, utf8_decode("Période du ") . date_format(date_create($datedebut), "d-m-Y") . " au " . date_format(date_create($datedefin), "d-m-Y"), 0, 1, 'L', false);


      $pdf->Ln(18);
      $pdf->SetX(15);
      $pdf->SetFillColor(230, 230, 0);
      $pdf->SetLineWidth(.3);
      $pdf->SetFont('ARIAL', 'B', 10);
      $pdf->Cell(8, 8, utf8_decode('N°'), 1, 0, 'C');
      $pdf->Cell(20, 8, utf8_decode('Date'), 1, 0, 'C');
      $pdf->Cell(70, 8, utf8_decode('Bénéficiaires'), 1, 0, 'C');
      $pdf->Cell(27, 8, utf8_decode('Classes'), 1, 0, 'C');
      $pdf->Cell(37, 8, utf8_decode('Forfaits'), 1, 0, 'C');
      $pdf->Cell(32, 8, utf8_decode('Montant dû '), 1, 0, 'C');
      $pdf->Cell(37, 8, utf8_decode('Montant versé '), 1, 0, 'C');

      $pdf->Cell(32, 8, utf8_decode('Solde'), 1, 0, 'C');

      $pdf->Ln();
      $pdf->SetFont('Arial', '', 10);
      $pdf->SetFillColor(96, 96, 96);



      $versements = DB::table('versement')
         ->join('users', 'users.id', '=', 'versement.ideleve_versement')
         ->join('classe', 'classe.id_classe', '=', 'versement.classe_versement')
         ->where('codeEtab_versement', '=', $codeEtab)
         ->where('session_versement', '=', $sessionEtab)
         ->where('motif_versement', '=', 'CANTINES')
         ->where('date_versement', '>=', $datedebut)
         ->where('date_versement', '<', $datedefin)
         ->orderByRaw('versement.id_versement DESC')
         ->get();
      $i = 1;
      foreach ($versements as $value):
         $pdf->SetX(15);
         $pdf->Cell(8, 7, $i, 1, 0, 'C');
         $pdf->Cell(20, 7, date_format(date_create($value->date_versement), "d/m/y"), 1, 0, 'C');
         $pdf->Cell(70, 7, substr($value->nom_users . ' ' . $value->prenom_users, 0, 20), 1, 0, 'L');
         $libelleclasse = $value->libelle_classe;
         $tabclasse = explode("/", $libelleclasse);
         $pdf->Cell(27, 7, $tabclasse[0], 1, 0, 'C');
         $pdf->Cell(37, 7, $value->trajet_versement, 1, 0, 'C');

         $pdf->Cell(32, 7, number_format($value->montantapayer_versement, 0, ',', ' '), 1, 0, 'C');
         $pdf->Cell(37, 7, number_format(($value->montant_versement), 0, ',', ' '), 1, 0, 'C');

         $pdf->Cell(32, 7, number_format($value->solde_versement, 0, ',', ' '), 1, 0, 'C');
         $pdf->Ln();
         $i++;

      endforeach;



      $pdf->Output();
      exit;
   }
}
