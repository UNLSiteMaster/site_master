<?php
$data = array();
$data['percent_complete'] = $context->getProgressPercent();

echo json_encode($data);