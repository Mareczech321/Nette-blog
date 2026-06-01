<?php
declare(strict_types=1);

namespace App\Model\Facade;

use Mpdf\Mpdf;
use Nette\Application\Responses\CallbackResponse;
use Nette\Http\IRequest;
use Nette\Http\IResponse;

final class PDFFacade
{
    public function createPdfResponse(string $html, string $filename): CallbackResponse
    {
        return new CallbackResponse(
            function (IRequest $httpRequest, IResponse $httpResponse) use ($html, $filename): void {
                if (ob_get_length()) ob_end_clean();

                $mpdf = new Mpdf([
                    'mode' => 'utf-8',
                    'format' => 'A4',
                    'margin_left' => 20,
                    'margin_right' => 20,
                    'margin_top' => 20,
                    'margin_bottom' => 30,
                ]);

                $footerHtml = '
                <div style="border-top: 1px solid #e2e8f0; padding-top: 10px; font-size: 8pt; color: #94a3b8; text-align: center;">
                    Vygenerováno systémem Nette Blog | Strana {PAGENO}
                </div>';

                $mpdf->SetHTMLFooter($footerHtml);

                $mpdf->WriteHTML($html);
                $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
            }
        );
    }
}