<?php

namespace App\Service;

use App\Entity\Ticket;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\Writer\SvgWriter;

class TicketPdfGenerator
{
    public function generate(Ticket $ticket): string
    {
        $booking = $ticket->getBooking();
        $event   = $booking?->getEvent();
        $user    = $ticket->getUser();

        // Contenu unique du QR code pour ce ticket
        $qrContent = sprintf(
            'TICKET|id=%d|booking=%d|user=%d|seat=%s|event=%s',
            $ticket->getId(),
            $booking?->getId() ?? 0,
            $user?->getId() ?? 0,
            $ticket->getSeat() ?? '',
            $event?->getNom() ?? ''
        );

        $result = Builder::create()
            ->writer(new SvgWriter())
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->size(260)
            ->margin(10)
            ->build();

        $qrDataUri = $result->getDataUri();

        // Image de l'événement
        $eventImage = null;
        if ($event && $event->getImage()) {
            $image = $event->getImage();
            if (preg_match('#^https?://#i', $image)) {
                $eventImage = $image;
            } else {
                $eventImage = '/uploads/events/' . $image;
            }
        }

        $bannerStyle = 'background: linear-gradient(135deg,#4361ee,#4895ef);';
        if ($eventImage) {
            $bannerStyle = sprintf(
                "background-image:url('%s');",
                htmlspecialchars($eventImage, ENT_QUOTES)
            );
        }

        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: DejaVu Sans, sans-serif; background:#f3f4f6; }
                .ticket-wrapper {
                    max-width: 700px;
                    margin: 20px auto;
                    background: #ffffff;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 10px 30px rgba(15,23,42,.25);
                    border: 1px solid #e5e7eb;
                }
                .event-banner {
                    height: 220px;
                    background-size: cover;
                    background-position: center;
                    position: relative;
                }
                .event-banner::before {
                    content: "";
                    position: absolute;
                    inset: 0;
                    background: linear-gradient(to top, rgba(15,23,42,.8), rgba(15,23,42,.2));
                }
                .event-banner-inner {
                    position: absolute;
                    inset: 0;
                    display: flex;
                    align-items: flex-end;
                    padding: 18px 24px;
                    color: #f9fafb;
                }
                .event-title {
                    font-size: 22px;
                    font-weight: 800;
                    margin-bottom: 4px;
                }
                .event-meta {
                    font-size: 13px;
                    opacity: .95;
                }
                .content {
                    padding: 20px 24px 24px;
                    display: grid;
                    grid-template-columns: 1.4fr 1fr;
                    gap: 20px;
                }
                .info-section-title {
                    font-size: 14px;
                    font-weight: 700;
                    margin-bottom: 8px;
                    color: #111827;
                }
                .info-row {
                    font-size: 13px;
                    margin-bottom: 5px;
                    color: #374151;
                }
                .label {
                    font-weight: 600;
                }
                .qr-box {
                    border-left: 1px dashed #e5e7eb;
                    padding-left: 18px;
                    text-align: center;
                }
                .qr-title {
                    font-size: 13px;
                    font-weight: 600;
                    margin-bottom: 8px;
                    color: #111827;
                }
                .footer-note {
                    margin-top: 10px;
                    font-size: 11px;
                    color: #6b7280;
                }
            </style>
        </head>
        <body>';

        $html .= sprintf('
            <div class="ticket-wrapper">
                <div class="event-banner" style="%s">
                    <div class="event-banner-inner">
                        <div>
                            <div class="event-title">%s</div>
                            <div class="event-meta">
                                %s<br>
                                Prix : %s € • Place : %s
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content">
                    <div>
                        <div class="info-section-title">Informations du ticket</div>
                        <div class="info-row"><span class="label">Ticket ID :</span> #%d</div>
                        <div class="info-row"><span class="label">Réservation :</span> #%d</div>
                        <div class="info-row"><span class="label">Utilisateur :</span> %s</div>
                        <div class="info-row"><span class="label">Siège :</span> %s</div>
                        <div class="info-row"><span class="label">Statut :</span> %s</div>
                        <div class="info-row"><span class="label">Places restantes évén. :</span> %s</div>
                    </div>
                    <div class="qr-box">
                        <div class="qr-title">QR Code du ticket</div>
                        <img src="%s" alt="QR Code" style="width: 180px; height: 180px;">
                        <div class="footer-note">
                            Présentez ce QR code à l\'entrée pour valider votre ticket.
                        </div>
                    </div>
                </div>
            </div>
        ',
            $bannerStyle,
            htmlspecialchars($event?->getNom() ?? 'Événement', ENT_QUOTES),
            $event && $event->getDateEvent()
                ? $event->getDateEvent()->format('d/m/Y')
                : 'Date à venir',
            $event?->getPrix() ?? 0,
            $ticket->getSeat() ?? '-',
            $ticket->getId(),
            $booking?->getId() ?? 0,
            $user?->getEmail() ?? 'Invité',
            $ticket->getSeat() ?? '-',
            $ticket->getStatus(),
            $event ? ($event->getRemainingCapacity() . ' restantes') : 'N/A',
            $qrDataUri
        );

        $html .= '</body></html>';

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}


