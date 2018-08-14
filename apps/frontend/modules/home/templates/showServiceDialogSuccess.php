<?php
    if($data && !empty($dialogType) && $dialogType == homeActions::INFO_DIALOG) {
        include_partial('info_modal_data', array('data' => $data));
    } else if($data) {
		include_partial('service_action_modal_data', array('data' => $data, 'cls' => 'service-action-modal-contaner')); 
	}
	else
		echo "Нет данных";
?>