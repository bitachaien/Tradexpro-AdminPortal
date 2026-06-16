<?php

function deposit_status_text($input = null)
{
    $output = [
        STATUS_ACCEPTED => __('Accepted'),
        STATUS_PENDING => __('Pending'),
        STATUS_REJECTED => __('Rejected'),
    ];

    if (is_null($input)) {
        return $output;
    } else {
        return $output[$input] ?? "N/A";
    }
}

function transaction_filter_by($qeury_type = null)
{
    $output = [
        TRANSACTION_FILTER_ALL => __('All'),
        TRANSACTION_FILTER_BOT_TO_BOT => __('Bot To Bot'),
        TRANSACTION_FILTER_BOT_TO_USER => __('Bot To User'),
        TRANSACTION_FILTER_USER_TO_USER => __('User To User'),
    ];

    if (is_null($qeury_type)) {
        return $output;
    } else {
        return $output[$qeury_type] ?? "N/A";
    }
}
