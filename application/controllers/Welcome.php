<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function table()
	{
		$this->load->library('MYPDF');

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'utf-8', false);

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('PdfWithCodeigniter');
		$pdf->SetTitle('PdfWithCodeigniter');
		$pdf->SetSubject('PdfWithCodeigniter');
		$pdf->SetKeywords('TCPDF, PDF, example, test, codeigniter');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));


		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set font
		$pdf->SetFont('times', 'BI', 12);

		// add a page
		$pdf->AddPage();

		// set some text to print
		$txt = <<<EOD
<h1>Student  list</h1>
<table cellspacing="0" cellpadding="1" border="1" style="border-color:gray;">
    <tr style="background-color:green;color:white;">
        <td>SL no</td>
        <td>Name</td>
        <td>Roll No</td>
		<td>City</td>
    </tr>
    <tr>
        <td>1</td>
        <td>Divyasundar</td>
		<td>001</td>
		<td>Pune</td>
    </tr>
	<tr>
        <td>1</td>
        <td>Milan</td>
		<td>002</td>
		<td>Pune</td>
    </tr>
	<tr>
        <td>1</td>
        <td>Hritika</td>
		<td>003</td>
		<td>Pune</td>
    </tr>
</table>
EOD;

		// print a block of text using Write()
		$pdf->writeHTMLCell(0, $txt, '', 0, 'C', true, 0, false, false, 0);

		// ---------------------------------------------------------
		 ob_clean();
		//Close and output PDF document
		$pdf->Output('example_003.pdf', 'I');
	}
	public function index()
	{
		$this->load->library('MYPDF');

		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'utf-8', false);

		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('PdfWithCodeigniter');
		$pdf->SetTitle('PdfWithCodeigniter');
		$pdf->SetSubject('PdfWithCodeigniter');
		$pdf->SetKeywords('TCPDF, PDF, example, test, codeigniter');

		// set default header data
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));


		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		// set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		// set font
		$pdf->SetFont('times', 'BI', 12);

		// add a page
		$pdf->AddPage();

		// set some text to print
		$txt = <<<EOD
		TCPDF Codeigniter Example

		Custom page header and footer are defined by extending the TCPDF class and overriding the Header() and Footer() methods.
EOD;

		// print a block of text using Write()
		$pdf->Write(0, $txt, '', 0, 'C', true, 0, false, false, 0);

		// ---------------------------------------------------------
		 ob_clean();
		//Close and output PDF document
		$pdf->Output('example_003.pdf', 'I');
	}

}
