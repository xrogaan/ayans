<?php

function set_bolder($data) {
    return "<strong>$data</strong>";
}

function getCrc32($data) {
    return printf("0x%X\n", crc32($data));
}