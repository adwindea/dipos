<?php

function curToDec($number){
    return str_replace(',', '.', str_replace('.', '', $number));
}

function decToCur($number){
    return rtrim(rtrim(number_format($number, 2, ",", "."), '0'), ',');
}