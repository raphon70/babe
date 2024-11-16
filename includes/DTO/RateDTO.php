<?php

declare(strict_types=1);

class RateDTO
{
    public int $rateId;
    public ?string $rateTitle;
    public ?DateTime $dateFrom;
    public ?DateTime $dateTo;
    public ?array $applyDays;
    public ?array $startDays;
    public ?int $minBookingPeriod = 0;
    public ?int $maxBookingPeriod = 0;
    public float $priceFrom = 0;
    public ?array $priceGeneral;
    public ?array $pricesConditional;
    public ?int $rateOrder = 0;

    public static function instanceFromArray( array $data ): self{

        $instance = new self();

        if ( !empty($data['rate_id']) ){
            $instance->rateId = (int) $data['rate_id'];
        }

        if ( !empty($data['rate_title']) ){
            $instance->rateTitle = $data['rate_title'];
        }

        if ( !empty($data['rate_date_from']) ){
            $instance->dateFrom = new DateTime( $data['rate_date_from'] );
        }

        if ( !empty($data['rate_date_to']) ){
            $instance->dateTo = new DateTime( $data['rate_date_to'] );
        }

        if ( !empty($data['apply_days']) ){
            $instance->applyDays = $data['apply_days'];
        }

        if ( !empty($data['start_days']) ){
            $instance->startDays = $data['start_days'];
        }

        if ( !empty($data['min_booking_period']) ){
            $instance->minBookingPeriod = (int)$data['min_booking_period'];
        }

        if ( !empty($data['max_booking_period']) ){
            $instance->maxBookingPeriod = (int)$data['max_booking_period'];
        }

        if ( !empty($data['price_from']) ){
            $instance->priceFrom = (float)$data['price_from'];
        }

        if ( !empty($data['price_general']) ){
            $instance->priceGeneral = $data['price_general'];
        }

        if ( !empty($data['prices_conditional']) ){
            $instance->pricesConditional = $data['prices_conditional'];
        }

        if ( !empty($data['rate_order']) ){
            $instance->rateOrder = (int)$data['rate_order'];
        }

        return $instance;
    }
}
