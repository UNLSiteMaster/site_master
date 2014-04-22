<?php
$data = array();
$data['percent_complete'] = $context->getProgressPercent();
$data['queue_position']   = $context->getQueuePosition();
$data['status']           = $context->scan->status;
echo json_encode($data);