<?php

if ( ! is_null( ID ) )
{

    if ( isset( $_GET[ 'success' ] ) )
    {
        echo '
		<div class="alert alert-success">
			Операция успешно выполнена
		</div>';
    }

    if ( ACTION == 'service_edit' )
    {

        $query = "
			SELECT
				`ss`.*,
				`shipment`.`shipment_number`
				`service`.`service_name`,
				`account`.`account_name`
			FROM
				`" . $db_config['pref'] . "shipment_services` AS `ss`
            LEFT JOIN `" . $db_config['pref'] . "services` AS `service`
				ON `service`.`service_code` = `ss`.`service_code`
            INNER JOIN `" . $db_config['pref'] . "shipments` AS `shipment`
				ON `shipment`.`shipment_id` = `ss`.`shipment_id`
				AND `shipment`.`is_active` = 1
			INNER JOIN `" . $db_config['pref'] . "accounts` AS `account`
				ON `account`.`account_id` = `s`.`account_id`
				AND `account`.`is_active` = 1
			WHERE
				`ss`.`shipment_service_id` = " . ID . "
			LIMIT 1";

        $result = $db->query( $query );
        if ( $db->num_rows( $result ) != 0 )
        {

            $service = $db->fetch_assoc( $result );

            echo '
            <h1>Оказываемая услуга #' . $service[ 'shipment_invoice_id' ] . '</h1>
            
            <form action="" method="post">
                <input type="hidden" name="action" value="invoice-save" />
                <input type="hidden" name="id" value="' . (int) $invoice['shipment_invoice_id'] . '" />
                <table class="info">
                    <tr>
                        <th colspan="2" class="title">Информация</th>
                    </tr>
                    <tr>
                        <th>Аккаунт</th>
                        <td>
                            <a href="?show[]=settings&show[]=accounts&id=' . (int) $invoice['account_id'] . '">' . $invoice['account_name'] . '</a>
                        </td>
                    </tr>
                    <tr>
                        <th>Отправление</th>
                        <td>
                            <a href="?show[]=shipments&id=' . (int) $invoice['shipment_id'] . '">' . $invoice['shipment_number'] . '</a>
                        </td>
                    </tr>
                    <tr>
                        <th>Услуга</th>
                        <td>
                            ' . $invoice['datetime'] . '
                        </td>
                    </tr>
                    <tr>
                        <th>Тип счета</th>
                        <td>
                            ' . $invoice['invoice_type_code'] . '
                        </td>
                    </tr>
                    <tr>
                        <th>Сумма</th>
                        <td>
                            <input type="text" name="invoice_sum" value="' . htmlspecialchars( $invoice['invoice_sum'] ) . '" />
                        </td>
                    </tr>
                    <tr>
                        <th>Валюта</th>
                        <td>
                            <select name="currency_code" required="required">';

                            $query = "SELECT * FROM `" . $db_config['pref'] . "currencies` ORDER BY `currency_code`";
                            $res = $db->query( $query ) or die ( $db->last_error() );

                            while ( $r = $db->fetch_assoc( $res ) )
                            {
                                echo '
                                  <option value="' . $r['currency_code'] . '"' . ( $r['currency_code'] == $invoice['currency_code'] ? ' selected="selected"' : '' ) . '>' . $r['currency_code'] . '</option>';
                            }
                            echo '
						  </select>
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2" class="title">Оплата</th>
                    </tr>
                    <tr>
                        <th>Дата оплаты</th>
                        <td>
                            ' . ( $invoice['pay_date'] ?: '-' ) . '
                        </td>
                    </tr>
                    <tr>
                        <th>Платежный документ</th>
                        <td>
                            ' . ( $invoice['pay_doc'] ?: '-' ) . '
                        </td>
                    </tr>
                    <tr>
                        <th>Номер платежного документа</th>
                        <td>
                            ' . ( $invoice['pay_doc_num'] ?: '-' ) . '
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2" class="title">Настройки</th>
                    </tr>
                    <tr>
                        <th>Активен</th>
                        <td>
                            <input type="checkbox" name="is_active" value="1"' . ( $invoice['is_active'] ? ' checked="checked"' : '' ) . ' />
                        </td>
                    </tr>
                    <tr>
                        <th colspan="2">
                            <button type="submit">Сохранить</button>
                        </th>
                    </tr>
                </table>
            </form>';

        }
        $db->free_result( $result );

    }
    else
    {

        $query = "
			SELECT
				`p`.*,
				`s`.*,
				`a`.`account_name`,
				`u`.`username`
			FROM
				`" . $db_config[ 'pref' ] . "shipments` AS `s`
			INNER JOIN `" . $db_config[ 'pref' ] . "accounts` AS `a`
				ON `a`.`account_id` = `s`.`account_id`
				AND `a`.`is_active` = 1
			LEFT JOIN `" . $db_config[ 'pref' ] . "account_users` AS `u`
				ON `u`.`user_id` = `s`.`user_id`
			LEFT JOIN `" . $db_config[ 'pref' ] . "process` AS `p`
				ON `p`.`shipment_id` = `s`.`shipment_id`
			WHERE
				`s`.`shipment_id` = " . ID . "
			LIMIT 1";
        $result = $db->query( $query );
        if ( $db->num_rows( $result ) != 0 )
        {

            $shipment = $db->fetch_assoc( $result );
            $db->free_result( $result );

            echo '
			<h1>Отправление ' . $shipment[ 'shipment_number' ] . '</h1>';

            if ( ACTION == 'service_add' )
            {

                echo 'В разработке';

            } else if ( ACTION == 'xml' )
            {

                echo '<h2>XML</h2>';

                $xmls = array();

                foreach ( $systems as $system => $dir )
                {
                    if ( $system == 'VEEROUTE' )
                    {
                        $list = explode( "\n", shell_exec( 'grep -R "' . $shipment[ 'shipment_barcode' ] . '" ' . $dir . ' | cut -d: -f1' ) );
                        foreach ( $list as $file )
                        {
                            if ( empty( $file ) ) continue;
                            $xmls[] = array(
                                'system' => $system,
                                'file' => basename( $file ),
                                'created' => filemtime( $file )
                            );
                        }
                    } else
                    {
                        $list = scandir( $dir );
                        foreach ( $list as $file )
                        {
                            if ( mb_strpos( $file, $shipment[ 'shipment_barcode' ] ) !== FALSE )
                            {
                                $xmls[] = array(
                                    'system' => $system,
                                    'file' => $file,
                                    'created' => filemtime( $dir . '/' . $file )
                                );
                            }
                        }
                    }
                }

                usort( $xmls, function ( $a, $b )
                {
                    return $a[ 'created' ] > $b[ 'created' ];
                } );

                echo '
				<table class="info">
					<thead>
						<tr>
							<th>
								Дата
							</th>
							<th>
								Система
							</th>
							<th>
								Файл
							</th>
						</tr>
					</thead>
					<tbody>';
                foreach ( $xmls as $xml )
                {
                    echo '
						<tr>
							<td>
								' . date( 'Y-m-d H:i:s', $xml[ 'created' ] ) . '
							</td>
							<td>
								' . $xml[ 'system' ] . '
							</td>
							<td>
								<a href="?action=download_xml&system=' . $xml[ 'system' ] . '&file=' . $xml[ 'file' ] . '">
									' . $xml[ 'file' ] . '
								</a>
							</td>
						</tr>';
                }
                echo '
					</tbody>
				</table>';

            } else
            {

                echo '
				 <form action="" method="post">
					<input type="hidden" name="action" value="shipment-save" />
					<input type="hidden" name="shipment_id" value="' . (int) $shipment[ 'shipment_id' ] . '" />
					<table class="info">
						<tr>
							<th colspan="2" class="title">Информация отправления</th>
						</tr>
						<tr>
							<th>Аккаунт</th>
							<td>
								<a href="?show[]=settings&show[]=accounts&id=' . (int) $shipment[ 'account_id' ] . '">' . $shipment[ 'account_name' ] . '</a>
							</td>
						</tr>
						<tr>
							<th>Пользователь</th>
							<td>
								' . $shipment[ 'username' ] . '
							</td>
						</tr>
						<tr>
							<th>Дата создания</th>
							<td>
								' . $shipment[ 'date_create' ] . '
							</td>
						</tr>
						<tr>
							<th>Дата приезда курьера</th>
							<td>
								<input type="text" name="ready_date" value="' . htmlspecialchars( $shipment[ 'ready_date' ] ) . '" class="datepicker" data-format="dd.mm.yy" />
							</td>
						</tr>
						<tr>
							<th>Удобное время</th>
							<td>
								<input type="text" name="ready_time" value="' . htmlspecialchars( $shipment[ 'ready_time' ] ) . '" />
							</td>
						</tr>
						<tr>
							<th>Штрихкод</th>
							<td>
								' . $shipment[ 'shipment_barcode' ] . '
							</td>
						</tr>
						<tr>
							<th>Способ отправки</th>
							<td>
								' . $shipment[ 'delivery_from_code' ] . ' - ' . $shipment[ 'delivery_to_code' ] . '
							</td>
						</tr>
						<tr>
							<th width="250">Номер отправления</th>
							<td>
								' . $shipment[ 'shipment_number' ] . '
							</td>
						</tr>
						<tr>
							<th width="250">Тип отправки</th>
							<td>
								' . $shipment[ 'shipment_type' ] . '
							</td>
						</tr>
						<tr>
							<th>Активен</th>
							<td>
								<input type="checkbox" name="is_active" value="1"' . ( $shipment[ 'is_active' ] ? ' checked="checked"' : '' ) . ' />
							</td>
						</tr>';

                if ( $shipment[ 'shipment_type' ] == 1 )
                {
                    echo '
							<tr>
								<th>Хрупкое</th>
								<td>
									<input type="checkbox" name="is_fragile" value="1"' . ( $shipment[ 'is_fragile' ] ? ' checked="checked"' : '' ) . ' />
								</td>
							</tr>';
                }

                echo '
						<tr>
							<th>Поставщик FM</th>
							<td>
								<select name="vendor_fm_code">
									<option value="">-</option>';

                $query = "
									SELECT
										*
									FROM 
										`" . $db_config[ 'pref' ] . "vendors`
									WHERE
										`is_active` = 1
										OR `vendor_code` = '" . $db->real_escape_string( $shipment[ 'vendor_fm_code' ] ) . "'
									ORDER BY 
										`vendor_name`";
                $result = $db->query( $query );
                while ( $vendor = $db->fetch_assoc( $result ) )
                {
                    echo '
									<option value="' . $vendor[ 'vendor_code' ] . '"' . ( $shipment[ 'vendor_fm_code' ] == $vendor[ 'vendor_code' ] ? ' selected="selected"' : '' ) . '>' . $vendor[ 'vendor_name' ] . '</option>';
                }
                $db->free_result( $result );

                echo '
								</select>
							</td>
						</tr>
						<tr>
							<th>Поставщик LM</th>
							<td>
								<select name="vendor_lm_code">
									<option value="">-</option>';

                $query = "
									SELECT
										*
									FROM 
										`" . $db_config[ 'pref' ] . "vendors`
									WHERE
										`is_active` = 1
										OR `vendor_code` = '" . $db->real_escape_string( $shipment[ 'vendor_lm_code' ] ) . "'
									ORDER BY 
										`vendor_name`";
                $result = $db->query( $query );
                while ( $vendor = $db->fetch_assoc( $result ) )
                {
                    echo '
									<option value="' . $vendor[ 'vendor_code' ] . '"' . ( $shipment[ 'vendor_lm_code' ] == $vendor[ 'vendor_code' ] ? ' selected="selected"' : '' ) . '>' . $vendor[ 'vendor_name' ] . '</option>';
                }
                $db->free_result( $result );

                echo '
								</select>
							</td>
						</tr>
						<tr>
							<th>Системный статус</th>
							<td>
								<input type="text" name="status" value="' . $shipment[ 'status' ] . '" />
							</td>
						</tr>
						<tr>
							<th>Требуется возврат</th>
							<td>
								<input type="checkbox" name="need_return" value="1"' . ( $shipment[ 'need_return' ] ? ' checked="checked"' : '' ) . ' />
							</td>
						</tr>
						<tr>
							<th>Комментарий к возврату</th>
							<td>
								<input type="text" name="comments_need_return" value="' . $shipment[ 'comments_need_return' ] . '" />
							</td>
						</tr>
						<tr>
							<th>Номер в КСЕ</th>
							<td>
								<input type="text" name="cse_number" value="' . $shipment[ 'cse_number' ] . '" />
							</td>
						</tr>
						<tr>
							<th colspan="2" class="title">Заявленные показатели</th>
						</tr>
						<tr>
							<th width="250">Вес</th>
							<td>
								' . $shipment[ 'weight' ] . '
							</td>
						</tr>';

                if ( $shipment[ 'shipment_type' ] == 1 )
                {
                    echo '
							<tr>
								<th width="250">Ширина</th>
								<td>
									' . $shipment[ 'package_width' ] . '
								</td>
							</tr>
							<tr>
								<th width="250">Длина</th>
								<td>
									' . $shipment[ 'package_length' ] . '
								</td>
							</tr>
							<tr>
								<th width="250">Высота</th>
								<td>
									' . $shipment[ 'package_height' ] . '
								</td>
							</tr>';
                } else
                {
                    echo '
							<tr>
								<th width="250">Описание</th>
								<td>
									' . $shipment[ 'goods_description' ] . '
								</td>
							</tr>';
                }

                echo '
						<tr>
							<th colspan="2" class="title">Фактические показатели</th>
						</tr>
						<tr>
							<th width="250">Вес</th>
							<td>
								<input type="text" name="fact_weight" value="' . htmlspecialchars( $shipment[ 'fact_weight' ] ) . '" />
							</td>
						</tr>';

                if ( $shipment[ 'shipment_type' ] == 1 )
                {
                    echo '
							<tr>
								<th width="250">Ширина</th>
								<td>
									<input type="text" name="fact_package_width" value="' . htmlspecialchars( $shipment[ 'fact_package_width' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th width="250">Длина</th>
								<td>
									<input type="text" name="fact_package_length" value="' . htmlspecialchars( $shipment[ 'fact_package_length' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th width="250">Высота</th>
								<td>
									<input type="text" name="fact_package_height" value="' . htmlspecialchars( $shipment[ 'fact_package_height' ] ) . '" />
								</td>
							</tr>';
                } else
                {
                    echo '
							<tr>
								<th width="250">Описание</th>
								<td>
									<input type="text" name="fact_goods_description" value="' . htmlspecialchars( $shipment[ 'fact_goods_description' ] ) . '" />
								</td>
							</tr>';
                }

                echo '
						<tr>
							<th width="250">Примечание</th>
							<td>
								<input type="text" name="shipment_description" value="' . htmlspecialchars( $shipment[ 'shipment_description' ] ) . '" />
							</td>
						</tr>
						<tr>
							<th colspan="2" class="title">PROCESS</th>
						</tr>
						<tr>
							<th>WS_CreateCargoEx</th>
							<td>
								<input type="checkbox" name="WS_CreateCargoEx" value="1"' . ( ! $shipment[ 'WS_CreateCargoEx' ] ? ' checked="checked"' : '' ) . ' />
							</td>
						</tr>
						<tr>
							<th>WS_ImportCargoSpecification</th>
							<td>
								<input type="checkbox" name="WS_ImportCargoSpecification" value="1"' . ( ! $shipment[ 'WS_ImportCargoSpecification' ] ? ' checked="checked"' : '' ) . ' />
							</td>
						</tr>
						<tr>
							<th>WS_CancelCargo</th>
							<td>
								<input type="checkbox" name="WS_CancelCargo" value="1"' . ( ! $shipment[ 'WS_CancelCargo' ] ? ' checked="checked"' : '' ) . ' />
							</td>
						</tr>
						<tr>
							<th>VeerouteCargoCreatePickup</th>
							<td>
								<input type="checkbox" name="VeerouteCargoCreatePickup" value="1"' . ( ! $shipment[ 'VeerouteCargoCreatePickup' ] ? ' checked="checked"' : '' ) . ' />
							</td>
						</tr>
						<tr>
							<th>VeerouteCargoCreateDeliver</th>
							<td>
								<input type="checkbox" name="VeerouteCargoCreateDeliver" value="1"' . ( ! $shipment[ 'VeerouteCargoCreateDeliver' ] ? ' checked="checked"' : '' ) . ' />
							</td>
						</tr>
						<tr>
							<th>VeerouteCargoDelete</th>
							<td>
								<input type="checkbox" name="VeerouteCargoDelete" value="1"' . ( ! $shipment[ 'VeerouteCargoDelete' ] ? ' checked="checked"' : '' ) . ' />
							</td>
						</tr>
						<tr>
							<th>CseCargoCreate</th>
							<td>
								<input type="checkbox" name="CseCargoCreate" value="1"' . ( ! $shipment[ 'CseCargoCreate' ] ? ' checked="checked"' : '' ) . ' />
							</td>
						</tr>
						<tr>
							<th>CseCargoDelete</th>
							<td>
								<input type="checkbox" name="CseCargoDelete" value="1"' . ( ! $shipment[ 'CseCargoDelete' ] ? ' checked="checked"' : '' ) . ' />
							</td>
						</tr>
						<tr>
							<th colspan="2">
								<button type="submit">Сохранить</button>
							</th>
						</tr>
					</table>
				 </form>';

                $q = "
					SELECT
						*
					FROM
						`" . $db_config[ 'pref' ] . "shipment_subjects` AS `ss`
					WHERE
						`ss`.`shipment_id` = " . (int) $shipment[ 'shipment_id' ] . "
						AND `ss`.`subject_type_code` = 'sender'";
                $res = $db->query( $q ) or die( $db->last_error() );
                if ( $db->num_rows( $res ) != 0 )
                {

                    $subject = $db->fetch_assoc( $res );
                    $db->free_result( $res );

                    echo '
					<h2>Информация об отправителе</h2>

					<form action="" method="post">

						<input type="hidden" name="action" value="shipment-subject-save" />
						<input type="hidden" name="id" value="' . $subject[ 'shipment_id' ] . '" />
						<input type="hidden" name="shipment_subject_id" value="' . $subject[ 'shipment_subject_id' ] . '" />

						<table class="info">
							<tr>
								<th colspan="2" class="title">Информация об отправителе</th>
							</tr>
							<tr>
								<th>Наименование</th>
								<td>
								 <input type="text" name="subject_name" value="' . htmlspecialchars( $subject[ 'subject_name' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Страна</th>
								<td>
									<select name="country_code" required="required">';

                    $query = "SELECT * FROM `" . $db_config[ 'pref' ] . "countries` ORDER BY `country_code`";
                    $res = $db->query( $query ) or die ( $db->last_error() );

                    while ( $r = $db->fetch_assoc( $res ) )
                    {
                        echo '
										  <option value="' . $r[ 'country_code' ] . '"' . ( $r[ 'country_code' ] == $subject[ 'country_code' ] ? ' selected="selected"' : '' ) . '>' . $r[ 'country_code' ] . '</option>';
                    }
                    echo '
									  </select>
								</td>
							</tr>
							<tr>
								<th>Почтовый индекс</th>
								<td>
								 <input type="text" name="postcode" value="' . htmlspecialchars( $subject[ 'postcode' ] ) . '" />
								</td>
							</tr>
							<tr>
							<th>Физический адрес</th>
								<td>
									<input type="text" name="actual_address" value="' . htmlspecialchars( $subject[ 'actual_address' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Юридический адрес</th>
								<td>
									<input type="text" name="juridical_address" value="' . htmlspecialchars( $subject[ 'juridical_address' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Форматированный адрес</th>
								<td>
									<input type="text" name="formatted_address" value="' . htmlspecialchars( $subject[ 'formatted_address' ] ) . '" readonly="readonly" />
								</td>
							</tr>
							<tr>
								<th>Комментарий к адресу</th>
								<td>
									<input type="text" name="address_comment" value="' . htmlspecialchars( $subject[ 'address_comment' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>lon</th>
								<td>
									<a href="https://yandex.ru/maps/213/moscow/?mode=search&text=' . urlencode( $subject[ 'lat' ] . ' ' . $subject[ 'lon' ] ) . '" target="_blank">
										' . $subject[ 'lon' ] . '
									</a>
								</td>
							</tr>
							<tr>
								<th>lat</th>
								<td>
									<a href="https://yandex.ru/maps/213/moscow/?mode=search&text=' . urlencode( $subject[ 'lat' ] . ' ' . $subject[ 'lon' ] ) . '" target="_blank">
										' . $subject[ 'lat' ] . '
									</a>
								</td>
							</tr>
							<tr>
								<th>E-mail</th>
								<td>
								  <input type="text" name="contact_email" value="' . htmlspecialchars( $subject[ 'contact_email' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th colspan="2" class="title">График работы</th>
							</tr>
							<tr>
								<th>Работает с</th>
								<td>
									<input type="time" name="working_from" value="' . htmlspecialchars( $subject[ 'working_from' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Работает до</th>
								<td>
									<input type="time" name="working_to" value="' . htmlspecialchars( $subject[ 'working_to' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Обед с</th>
								<td>
									<input type="time" name="break_from" value="' . htmlspecialchars( $subject[ 'break_from' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Обед до</th>
								<td>
									<input type="time" name="break_to" value="' . htmlspecialchars( $subject[ 'break_to' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th colspan="2" class="title">График работы в выходные дни</th>
							</tr>
							<tr>
								<th>Работает с</th>
								<td>
									<input type="time" name="holiday_working_from" value="' . htmlspecialchars( $subject[ 'holiday_working_from' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Работает до</th>
								<td>
									<input type="time" name="holiday_working_to" value="' . htmlspecialchars( $subject[ 'holiday_working_to' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Обед с</th>
								<td>
									<input type="time" name="holiday_break_from" value="' . htmlspecialchars( $subject[ 'holiday_break_from' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Обед до</th>
								<td>
									<input type="time" name="holiday_break_to" value="' . htmlspecialchars( $subject[ 'holiday_break_to' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th colspan="2" class="title">Телефон</th>
							</tr>
							<tr>
								<th>Код</th>
								<td>
								  <input type="text" name="contact_phone_code" value="' . htmlspecialchars( $subject[ 'contact_phone_code' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Номер</th>
								<td>
								  <input type="text" name="contact_phone_number" value="' . htmlspecialchars( $subject[ 'contact_phone_number' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th colspan="2" class="title">Настройки</th>
							</tr>
							<tr>
								<th>Активен</th>
								<td>
									<input type="checkbox" name="is_active" value="1"' . ( $subject[ 'is_active' ] ? ' checked="checked"' : '' ) . ' />
								</td>
							</tr>
							<tr>
								<th>Требуется пропуск</th>
								<td>
									<input type="checkbox" name="need_pass" value="1"' . ( $subject[ 'need_pass' ] ? ' checked="checked"' : '' ) . ' />
								</td>
							</tr>
							<tr>
								<th>Ожидание в очереди</th>
								<td>
									<input type="checkbox" name="need_waiting" value="1"' . ( $subject[ 'need_waiting' ] ? ' checked="checked"' : '' ) . ' />
								</td>
							</tr>
							<tr>
								<th>Юр. лицо</th>
								<td>
									<input type="checkbox" name="is_juridical" value="1"' . ( $subject[ 'is_juridical' ] ? ' checked="checked"' : '' ) . ' />
								</td>
							</tr>
							<tr>
								<th>Комментарий Сорт. центра</th>
								<td>
									' . $subject[ 'comm_sort_centr' ] . '
								</td>
							</tr>
							<tr>
								<th colspan="2">
									<button type="submit">Сохранить</button>
								</th>
							</tr>
						</table>
					</form>';

                }

                $q = "
					SELECT
						*
					FROM
						`" . $db_config[ 'pref' ] . "shipment_subjects` AS `ss`
					WHERE
						`ss`.`shipment_id` = " . (int) $shipment[ 'shipment_id' ] . "
						AND `ss`.`is_active` = 1
						AND `ss`.`subject_type_code` = 'receiver'";
                $res = $db->query( $q ) or die( $db->last_error() );
                if ( $db->num_rows( $res ) != 0 )
                {

                    $subject = $db->fetch_assoc( $res );
                    $db->free_result( $res );

                    echo '
					<h2>Информация о получателе</h2>

					<form action="" method="post">

						<input type="hidden" name="action" value="shipment-subject-save" />
						<input type="hidden" name="id" value="' . $subject[ 'shipment_id' ] . '" />
						<input type="hidden" name="shipment_subject_id" value="' . $subject[ 'shipment_subject_id' ] . '" />

						<table class="info">
							<tr>
								<th colspan="2" class="title">Информация о получателе</th>
							</tr>
							<tr>
								<th>Наименование</th>
								<td>
								 <input type="text" name="subject_name" value="' . htmlspecialchars( $subject[ 'subject_name' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Страна</th>
								<td>
									<select name="country_code" required="required">';

                    $query = "SELECT * FROM `" . $db_config[ 'pref' ] . "countries` ORDER BY `country_code`";
                    $res = $db->query( $query ) or die ( $db->last_error() );

                    while ( $r = $db->fetch_assoc( $res ) )
                    {
                        echo '
										  <option value="' . $r[ 'country_code' ] . '"' . ( $r[ 'country_code' ] == $subject[ 'country_code' ] ? ' selected="selected"' : '' ) . '>' . $r[ 'country_code' ] . '</option>';
                    }
                    echo '
									  </select>
								</td>
							</tr>
							<tr>
								<th>Почтовый индекс</th>
								<td>
								 <input type="text" name="postcode" value="' . htmlspecialchars( $subject[ 'postcode' ] ) . '" />
								</td>
							</tr>
							<tr>
							<th>Физический адрес</th>
								<td>
									<input type="text" name="actual_address" value="' . htmlspecialchars( $subject[ 'actual_address' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Юридический адрес</th>
								<td>
									<input type="text" name="juridical_address" value="' . htmlspecialchars( $subject[ 'juridical_address' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Форматированный адрес</th>
								<td>
									<input type="text" name="formatted_address" value="' . htmlspecialchars( $subject[ 'formatted_address' ] ) . '" readonly="readonly" />
								</td>
							</tr>
							<tr>
								<th>Комментарий к адресу</th>
								<td>
									<input type="text" name="address_comment" value="' . htmlspecialchars( $subject[ 'address_comment' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>lon</th>
								<td>
									<a href="https://yandex.ru/maps/213/moscow/?mode=search&text=' . urlencode( $subject[ 'lat' ] . ' ' . $subject[ 'lon' ] ) . '" target="_blank">
										' . $subject[ 'lon' ] . '
									</a>
								</td>
							</tr>
							<tr>
								<th>lat</th>
								<td>
									<a href="https://yandex.ru/maps/213/moscow/?mode=search&text=' . urlencode( $subject[ 'lat' ] . ' ' . $subject[ 'lon' ] ) . '" target="_blank">
										' . $subject[ 'lat' ] . '
									</a>
								</td>
							</tr>
							<tr>
								<th>E-mail</th>
								<td>
								  <input type="text" name="contact_email" value="' . htmlspecialchars( $subject[ 'contact_email' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>ИНН</th>
								<td>
								  <input type="text" name="inn" value="' . htmlspecialchars( $subject[ 'inn' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th colspan="2" class="title">График работы</th>
							</tr>
							<tr>
								<th>Работает с</th>
								<td>
									<input type="time" name="working_from" value="' . htmlspecialchars( $subject[ 'working_from' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Работает до</th>
								<td>
									<input type="time" name="working_to" value="' . htmlspecialchars( $subject[ 'working_to' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Обед с</th>
								<td>
									<input type="time" name="break_from" value="' . htmlspecialchars( $subject[ 'break_from' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Обед до</th>
								<td>
									<input type="time" name="break_to" value="' . htmlspecialchars( $subject[ 'break_to' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th colspan="2" class="title">График работы в выходные дни</th>
							</tr>
							<tr>
								<th>Работает с</th>
								<td>
									<input type="time" name="holiday_working_from" value="' . htmlspecialchars( $subject[ 'holiday_working_from' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Работает до</th>
								<td>
									<input type="time" name="holiday_working_to" value="' . htmlspecialchars( $subject[ 'holiday_working_to' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Обед с</th>
								<td>
									<input type="time" name="holiday_break_from" value="' . htmlspecialchars( $subject[ 'holiday_break_from' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Обед до</th>
								<td>
									<input type="time" name="holiday_break_to" value="' . htmlspecialchars( $subject[ 'holiday_break_to' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th colspan="2" class="title">Телефон</th>
							</tr>
							<tr>
								<th>Код</th>
								<td>
								  <input type="text" name="contact_phone_code" value="' . htmlspecialchars( $subject[ 'contact_phone_code' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Номер</th>
								<td>
								  <input type="text" name="contact_phone_number" value="' . htmlspecialchars( $subject[ 'contact_phone_number' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th colspan="2" class="title">Паспортные данные</th>
							</tr>
							<tr>
								<th>Фамилия</th>
								<td>
								 <input type="text" name="subject_lastname" value="' . htmlspecialchars( $subject[ 'subject_lastname' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Имя</th>
								<td>
								 <input type="text" name="subject_firstname" value="' . htmlspecialchars( $subject[ 'subject_firstname' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Отчество</th>
								<td>
								 <input type="text" name="subject_middlename" value="' . htmlspecialchars( $subject[ 'subject_middlename' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Гражданство</th>
								<td>
								 <input type="text" name="passport_nationality" value="' . htmlspecialchars( $subject[ 'passport_nationality' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Серия</th>
								<td>
								 <input type="text" name="passport_series" value="' . htmlspecialchars( $subject[ 'passport_series' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Номер</th>
								<td>
								 <input type="text" name="passport_number" value="' . htmlspecialchars( $subject[ 'passport_number' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Дата выдачи</th>
								<td>
								 <input type="text" name="passport_date" value="' . htmlspecialchars( $subject[ 'passport_date' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th>Кем выдано</th>
								<td>
								 <input type="text" name="passport_authority" value="' . htmlspecialchars( $subject[ 'passport_authority' ] ) . '" />
								</td>
							</tr>
							<tr>
								<th colspan="2" class="title">Настройки</th>
							</tr>
							<tr>
								<th>Активен</th>
								<td>
									<input type="checkbox" name="is_active" value="1"' . ( $subject[ 'is_active' ] ? ' checked="checked"' : '' ) . ' />
								</td>
							</tr>
							<tr>
								<th>Требуется пропуск</th>
								<td>
									<input type="checkbox" name="need_pass" value="1"' . ( $subject[ 'need_pass' ] ? ' checked="checked"' : '' ) . ' />
								</td>
							</tr>
							<tr>
								<th>Ожидание в очереди</th>
								<td>
									<input type="checkbox" name="need_waiting" value="1"' . ( $subject[ 'need_waiting' ] ? ' checked="checked"' : '' ) . ' />
								</td>
							</tr>
							<tr>
								<th>Юр. лицо</th>
								<td>
									<input type="checkbox" name="is_juridical" value="1"' . ( $subject[ 'is_juridical' ] ? ' checked="checked"' : '' ) . ' />
								</td>
							</tr>
							<tr>
								<th>Комментарий Сорт. центра</th>
								<td>
								  ' . $subject[ 'comm_sort_centr' ] . '
								</td>
							</tr>
							<tr>
								<th colspan="2">
									<button type="submit">Сохранить</button>
								</th>
							</tr>
						</table>
					</form>';

                }

                echo '
				<h2 id="services">Оказываемые услуги</h2>';

                $q = "
					SELECT
						`ss`.*,
						`s`.`service_name`
					FROM
						`" . $db_config[ 'pref' ] . "shipment_services` AS `ss`
					INNER JOIN `" . $db_config[ 'pref' ] . "services` AS `s`
						ON `s`.`service_code` = `ss`.`service_code`
					WHERE
						`ss`.`shipment_id` = " . (int) $shipment[ 'shipment_id' ] . "
					ORDER BY
						`ss`.`shipment_service_id`";
                $res = $db->query( $q ) or die( $db->last_error() );
                if ( $db->num_rows( $res ) != 0 )
                {

                    echo '
					<table class="info">
						<tr>
							<th class="title">
								ID
							</th>
							<th class="title">
								Код услуги
							</th>
							<th class="title">
								Наименование услуги
							</th>
							<th class="title">
								Стоимость
							</th>
							<th class="title">
								Скидка \ наценка
							</th>
							<th class="title">
								Кол-во
							</th>
							<th class="title">
								Итого
							</th>
							<th class="title">
								Валюта
							</th>
							<th class="title" width="16">
								*
							</th>
						</tr>';

                    while ( $service = $db->fetch_assoc( $res ) )
                    {
                        echo '
						<tr>
							<td>
								<a href="?show[]=shipments&action=service_edit&id=' . (int) $service[ 'shipment_service_id' ] . '">
									' . $service[ 'shipment_service_id' ] . '
								</a>
							</td>
							<td>
								<a href="?show[]=settings&show[]=services&code=' . $service[ 'service_code' ] . '">
									' . $service[ 'service_code' ] . '
								</a>
							</td>
							<td>
								' . $service[ 'service_name' ] . '
							</td>
							<td>
								' . $service[ 'service_price' ] . '
							</td>
							<td>
								' . ( $service[ 'condition_value' ] ?: '-' ) . '
							</td>
							<td>
								' . $service[ 'count' ] . '
							</td>
							<td>
								' . $service[ 'total_price' ] . '
							</td>
							<td>
								' . $service[ 'currency_code' ] . '
							</td>
							<th>
									' . ( $service[ 'is_active' ] ? '<img src="img/icon_ok_16.png" title="Активен" />' : '<img src="img/icon_no_16.png" title="Отменен" />' ) . '
							</th>
						</tr>';
                    }

                    $db->free_result( $res );

                    echo '
					</table>';

                }

                echo '
				<ul class="links">
					<li>
						<a href="?show[]=shipments&action=service_add&id=' . (int) $shipment[ 'shipment_id' ] . '">Добавить</a>
					</li>
				</ul>';

                echo '
				<h2 id="invoices">Выставленные счета</h2>';

                $q = "
					SELECT
						`si`.*
					FROM
						`" . $db_config[ 'pref' ] . "shipment_invoices` AS `si`
					WHERE
						`si`.`shipment_id` = " . (int) $shipment[ 'shipment_id' ] . "
					ORDER BY
						`si`.`datetime`,
						`si`.`shipment_invoice_id`";
                $res = $db->query( $q ) or die( $db->last_error() );
                if ( $db->num_rows( $res ) != 0 )
                {

                    echo '
					<table class="info">
						<tr>
							<th class="title" rowspan="2">
								Тип счета
							</th>
							<th class="title" rowspan="2">
								Номер счета
							</th>
							<th class="title" rowspan="2">
								Дата
							</th>
							<th class="title" rowspan="2">
								Сумма
							</th>
							<th class="title" rowspan="2">
								Валюта
							</th>
							<th class="title text-center" colspan="3">
								Оплата
							</th>
							<th class="title" width="16">
								*
							</th>
						</tr>
						<tr>
							<th class="title">
								Дата платежа
							</th>
							<th class="title">
								Платежный документ
							</th>
							<th class="title">
								Номер платежного документа
							</th>
							<th class="title">

							</th>
						</tr>';

                    $invoice_total = 0;
                    $currency_code = '';
                    while ( $invoice = $db->fetch_assoc( $res ) )
                    {
                        if ( $invoice[ 'is_active' ] )
                        {
                            $invoice_total += $invoice[ 'invoice_sum' ];
                        }
                        $currency_code = $invoice[ 'currency_code' ];
                        echo '
						<tr>
							<td>
								' . $invoice[ 'invoice_type_code' ] . '
							</td>
							<td>
								<a href="?show[]=invoices&id=' . (int) $invoice[ 'shipment_invoice_id' ] . '">
									' . $invoice[ 'shipment_invoice_id' ] . '
								</a>
							</td>
							<td>
								' . $invoice[ 'datetime' ] . '
							</td>
							<td style="text-align: right;">
								' . $invoice[ 'invoice_sum' ] . '
							</td>
							<td>
								' . $invoice[ 'currency_code' ] . '
							</td>';

                        if ( $invoice[ 'invoice_sum' ] > 0 )
                        {
                            echo '
								<td>
									' . ( $invoice[ 'paid_date' ] ?: '-' ) . '
								</td>
								<td>
									' . ( $invoice[ 'paid_doc' ] ?: '-' ) . '
								</td>
								<td>
									' . ( $invoice[ 'paid_doc_num' ] ?: '-' ) . '
								</td>';
                        } else
                        {
                            echo '
								<td colspan="3">
									-
								</td>';
                        }
                        echo '
							<th>
									' . ( $invoice[ 'is_active' ] ? '<img src="img/icon_ok_16.png" title="Активен" />' : '<img src="img/icon_no_16.png" title="Отменен" />' ) . '
							</th>
						</tr>';
                    }

                    $db->free_result( $res );

                    echo '
						<tr>
							<th style="text-align: right;" colspan="3">
								Итого
							</th>
							<th style="text-align: right;">
								' . number_format( $invoice_total, 2, '.', '' ) . '
							</th>
							<th>
								' . $currency_code . '
							</th>
							<th colspan="4">
								&nbsp;
							</th>
						</tr>
					</table>';

                }

                echo '
				<ul class="links">
					<li>
						<a href="?show[]=invoices&action=add&shipment=' . (int) $shipment[ 'shipment_id' ] . '">Выставить счет вручную</a>
					</li>';
                if ( $shipment[ 'is_active' ] )
                {
                    echo '
						<li>
							<a href="?show[]=invoices&action=update&shipment=' . (int) $shipment[ 'shipment_id' ] . '">Выставить счет автоматически</a>
						</li>';
                }
                echo '
				</ul>';

                $q = "
					SELECT
						`ss`.`datetime`,
						`ss`.`location`,
						`s`.`status_text`,
						`ss`.`weight`,
						`ss`.`vendor_comment`
					FROM
						`" . $db_config[ 'pref' ] . "shipment_statuses` AS `ss`
					INNER JOIN `" . $db_config[ 'pref' ] . "statuses` AS `s`
						ON `s`.`status_code` = `ss`.`status_code`
						AND `s`.`lang_code` = 'ru'
					WHERE
						`ss`.`shipment_id` = " . (int) $shipment[ 'shipment_id' ] . "
						AND `ss`.`is_active` = 1
					ORDER BY
						`ss`.`datetime`";
                $res = $db->query( $q ) or die( $db->last_error() );
                if ( $db->num_rows( $res ) != 0 )
                {

                    echo '
					<h2>Публичные статусы</h2>

					<table class="info">
						<tr>
							<th class="title">
								Дата статуса
							</th>
							<th class="title">
								Статус
							</th>
							<th class="title">
								Местоположение
							</th>
							<th class="title">
								Вес
							</th>
							<th class="title">
								Комментарий поставщика
							</th>
						</tr>';

                    while ( $status = $db->fetch_assoc( $res ) )
                    {
                        echo '
						<tr>
							<td>
								' . $status[ 'datetime' ] . '
							</td>
							<td>
								' . $status[ 'status_text' ] . '
							</td>
							<td>
								' . $status[ 'location' ] . '
							</td>
							<td>
								' . $status[ 'weight' ] . '
							</td>
							<td>
								' . $status[ 'vendor_comment' ] . '
							</td>
						</tr>';
                    }

                    $db->free_result( $res );

                    echo '
					</table>';

                }

                echo '
				<h2>Сменить публичный статус</h2>

				<form action="" method="post">

					<input type="hidden" name="action" value="status-save" />
					<input type="hidden" name="id" value="' . (int) $shipment[ 'shipment_id' ] . '" />

					<table class="info">
						<tr>
							<th>Дата статуса</th>
							<td>
							 <input type="text" name="datetime" value="" />
							</td>
						</tr>
						<tr>
						  <th>
							  Статус
						  </th>
						  <td>
							  <select name="status_code">
								<option value=""> - выберите из списка - </option>';

                $q = "
								SELECT
									*
								FROM
									`" . $db_config[ 'pref' ] . "statuses` AS `s`
								WHERE
									`s`.`lang_code` = 'ru'
									AND `s`.`is_active` = 1";
                $res = $db->query( $q ) or die( $db->last_error() );
                while ( $status = $db->fetch_assoc( $res ) )
                {
                    echo '
								<option value="' . $status[ 'status_code' ] . '">' . $status[ 'status_text' ] . '</option>';
                }

                echo '
							  </select>
						  </td>
						</tr>
						<tr>
							<th>Местоположение</th>
							<td>
							 <input type="text" name="location" value="" />
							</td>
						</tr>
						<tr>
							<th>Вес</th>
							<td>
							 <input type="text" name="weight" value="' . ( ! is_null( $shipment[ 'fact_weight' ] ) ? $shipment[ 'fact_weight' ] : $shipment[ 'weight' ] ) . '" />
							</td>
						</tr>
						<tr>
						  <th colspan="2">
							  <button>Сохранить</button>
						  </th>
						</tr>
					</table>

				</form>';

                $q = "
					SELECT
						`svs`.`datetime`,
						`vs`.`status_name`,
						`svs`.`comment`,
						`svs`.`type`,
						`vs`.`vendor_code`
					FROM
						`" . $db_config[ 'pref' ] . "shipment_vendor_statuses` AS `svs`
					INNER JOIN `" . $db_config[ 'pref' ] . "vendor_statuses` AS `vs`
						ON `vs`.`status_code` = `svs`.`status_code`
					WHERE
						`svs`.`shipment_id` = " . (int) $shipment[ 'shipment_id' ] . "
					ORDER BY
						`svs`.`datetime`";
                $res = $db->query( $q ) or die( $db->last_error() );
                if ( $db->num_rows( $res ) != 0 )
                {

                    echo '
					<h2>Статусы поставщиков</h2>

					<table class="info">
						<tr>
							<th class="title">
								Дата статуса
							</th>
							<th class="title">
								Поставщик
							</th>
							<th class="title">
								Тип
							</th>
							<th class="title">
								Статус
							</th>
							<th class="title">
								Комментарий
							</th>
						</tr>';

                    while ( $status = $db->fetch_assoc( $res ) )
                    {
                        echo '
						<tr>
							<td>
								' . $status[ 'datetime' ] . '
							</td>
							<td>
								' . $status[ 'vendor_code' ] . '
							</td>
							<td>
								' . $status[ 'type' ] . '
							</td>
							<td>
								' . $status[ 'status_name' ] . '
							</td>
							<td>
								' . $status[ 'comment' ] . '
							</td>
						</tr>';
                    }

                    $db->free_result( $res );

                    echo '
					</table>';

                }

                echo '
				<h2>ЛОГИ</h2>';

                $q = "CALL get_process_logs('" . $shipment[ 'shipment_barcode' ] . "')";
                $r = $db->query( $q ) or die( $db->last_error() );
                echo '
				<table class="info">
					<thead>
						<tr>
							<th>
								Дата
							</th>
							<th>
								Действие
							</th>
							<th>
								Текст
							</th>
						</tr>
					</thead>
					<tbody>';
                while ( $log = $db->fetch_assoc( $r ) )
                {
                    echo '
						<tr>
							<td>
								' . $log[ 'datetime' ] . '
							</td>
							<td>
								' . $log[ 'action' ] . '
							</td>
							<td>
								' . $log[ 'text' ] . '
							</td>
						</tr>';
                }
                $db->free_result( $r );
                echo '
					</tbody>
				</table>';

                echo '
				<h2>
					<a href="?show[]=shipments&action=xml&id=' . $shipment[ 'shipment_id' ] . '">
						XML
					</a>
				</h2>';

                $db->free_result( $r );

            }

        } else echo '<div class="not_found">Ничего не найдено</div>';

    }

}
else
{

        $sorts = array(
            'shipment_id' => 'Дате создания',
            'shipment_number' => 'Номеру отправления'
        );

        $account = isset( $_GET[ 'account' ] ) ? trim( $_GET[ 'account' ] ) : '';
        $number = isset( $_GET[ 'number' ] ) ? trim( $_GET[ 'number' ] ) : $search;
        $barcode = isset( $_GET[ 'barcode' ] ) ? trim( $_GET[ 'barcode' ] ) : '';
        $receiver_firstname = isset( $_GET[ 'receiver_firstname' ] ) ? trim( $_GET[ 'receiver_firstname' ] ) : '';
        $receiver_lastname = isset( $_GET[ 'receiver_lastname' ] ) ? trim( $_GET[ 'receiver_lastname' ] ) : '';
        $receiver_middlename = isset( $_GET[ 'receiver_middlename' ] ) ? trim( $_GET[ 'receiver_middlename' ] ) : '';
        $sort = isset( $_GET[ 'sort' ] ) && isset( $sorts[ $_GET[ 'sort' ] ] ) ? trim( $_GET[ 'sort' ] ) : 'shipment_id';
        $sort_ad = isset( $_GET[ 'sort_ad' ] ) && ( $_GET[ 'sort_ad' ] == 'DESC' || $_GET[ 'sort_ad' ] == 'ASC' ) ? trim( $_GET[ 'sort_ad' ] ) : 'DESC';

        $period_from = isset( $_GET[ 'period_from' ] ) ? trim( $_GET[ 'period_from' ] ) : '';
        $period_to = isset( $_GET[ 'period_to' ] ) ? trim( $_GET[ 'period_to' ] ) : '';

        $vendor_fm_code = isset( $_GET[ 'vendor_fm_code' ] ) ? trim( $_GET[ 'vendor_fm_code' ] ) : '';
        $vendor_lm_code = isset( $_GET[ 'vendor_lm_code' ] ) ? trim( $_GET[ 'vendor_lm_code' ] ) : '';

        $service_code = isset( $_GET[ 'service_code' ] ) ? $_GET[ 'service_code' ] : array();
        $add_service_code = isset( $_GET[ 'add_service_code' ] ) ? $_GET[ 'add_service_code' ] : array();

        echo '
	<h1>' . $title . '</h1>
	
	<form action="#result" method="get">';

        foreach ( $show as $s )
        {
            echo '
         <input type="hidden" name="show[]" value="' . $s . '" />';
        }

        echo '
		<div class="form-row">
			<label for="account">Аккаунт</label>
			<input type="text" id="account" name="account" placeholder="Аккаунт" value="' . htmlspecialchars( $account ) . '" />
		</div>
	
		<div class="form-row">
			<label for="number">Номер отправления</label>
			<input type="text" id="number" name="number" placeholder="Номер отправления" value="' . htmlspecialchars( $number ) . '" />
		</div>
		
		<div class="form-row">
			<label for="number">Штрихкод</label>
			<input type="text" id="barcode" name="barcode" placeholder="Штрихкод" value="' . htmlspecialchars( $barcode ) . '" />
		</div>
		
		<div class="form-title">
			Информация заказчика
		</div>
		
		<div class="form-row">
			<label for="receiver_firstname">Фамилия</label>
			<input type="text" id="receiver_lastname" name="receiver_lastname" placeholder="Фамилия" value="' . htmlspecialchars( $receiver_lastname ) . '" />
		</div>
		
		<div class="form-row">
			<label for="receiver_firstname">Имя</label>
			<input type="text" id="receiver_firstname" name="receiver_firstname" placeholder="Имя" value="' . htmlspecialchars( $receiver_firstname ) . '" />
		</div>
		
		<div class="form-row">
			<label for="receiver_firstname">Отчество</label>
			<input type="text" id="receiver_middlename" name="receiver_middlename" placeholder="Отчество" value="' . htmlspecialchars( $receiver_middlename ) . '" />
		</div>
		
		<div class="form-title">
			Поставщик
		</div>
		
		<div class="form-row">
			<label for="vendor_fm_code">Первая миля</label>
			<select name="vendor_fm_code">
            <option value="">-</option>';

        $query = "
            SELECT
				*
            FROM 
				`" . $db_config[ 'pref' ] . "vendors`
			WHERE
				`is_active` = 1
            ORDER BY 
				`vendor_name`";
        $result = $db->query( $query );
        while ( $vendor = $db->fetch_assoc( $result ) )
        {
            echo '
            <option value="' . $vendor[ 'vendor_code' ] . '"' . ( $vendor_fm_code == $vendor[ 'vendor_code' ] ? ' selected="selected"' : '' ) . '>' . $vendor[ 'vendor_name' ] . '</option>';
        }
        $db->free_result( $result );

        echo '
         </select>
		</div>
		
		<div class="form-row">
			<label for="vendor_lm_code">Последняя миля</label>
			<select name="vendor_lm_code">
            <option value="">-</option>';

        $query = "
            SELECT
				*
            FROM 
				`" . $db_config[ 'pref' ] . "vendors`
			WHERE
				`is_active` = 1
            ORDER BY 
				`vendor_name`";
        $result = $db->query( $query );
        while ( $vendor = $db->fetch_assoc( $result ) )
        {
            echo '
            <option value="' . $vendor[ 'vendor_code' ] . '"' . ( $vendor_lm_code == $vendor[ 'vendor_code' ] ? ' selected="selected"' : '' ) . '>' . $vendor[ 'vendor_name' ] . '</option>';
        }
        $db->free_result( $result );

        echo '
         </select>
		</div>
		
		<div class="form-title">
			Услуги
		</div>
		
		<div class="form-row">
			<label for="service_code">Основная услуга</label>
			<select name="service_code[]" multiple="multiple">';

        $query = "
            SELECT *
            FROM `" . $db_config[ 'pref' ] . "services`
            WHERE	`is_main` = 1
            ORDER BY `service_name`";
        $result = $db->query( $query );
        while ( $service = $db->fetch_assoc( $result ) )
        {
            echo '
            <option value="' . $service[ 'service_code' ] . '"' . ( in_array( $service[ 'service_code' ], $service_code ) ? ' selected="selected"' : '' ) . '>' . $service[ 'service_name' ] . '</option>';
        }
        $db->free_result( $result );

        echo '
         </select>
		</div>
		
		<div class="form-row">
			<label for="add_service_code">Доп. услуга</label>
			<select name="add_service_code[]" multiple="multiple">';

        $query = "
            SELECT *
            FROM `" . $db_config[ 'pref' ] . "services`
            WHERE	`is_main` = 0
            ORDER BY `service_name`";
        $result = $db->query( $query );
        while ( $service = $db->fetch_assoc( $result ) )
        {
            echo '
            <option value="' . $service[ 'service_code' ] . '"' . ( in_array( $service[ 'service_code' ], $add_service_code ) ? ' selected="selected"' : '' ) . '>' . $service[ 'service_name' ] . '</option>';
        }
        $db->free_result( $result );

        echo '
         </select>
		</div>
		
		<div class="form-title">
			Период
		</div>
		
		<div class="form-row">
			<input type="text" id="period_from" name="period_from" placeholder="С" class="datepicker period" value="' . htmlspecialchars( $period_from ) . '" />
			-
			<input type="text" id="period_to" name="period_to" placeholder="По" class="datepicker period" value="' . htmlspecialchars( $period_to ) . '" />
		</div>
		
		<div class="form-title">
			Отображение
		</div>
		
		<div class="form-row">
			<label for="per_page">Кол-во строк на странице</label>
			<select id="per_page" name="per_page">';

        foreach ( $per_pages as $_per_page )
        {
            echo '
				<option' . ( LIMIT_PER_PAGE == $_per_page ? ' selected="selected"' : '' ) . '>' . $_per_page . '</option>';
        }

        echo '
			</select>
		</div>
		
		<div class="form-row">
			<label for="sort">Сортировать по</label>
			<select id="sort" name="sort">';

        foreach ( $sorts as $key => $val )
        {
            echo '
				<option value="' . $key . '"' . ( $sort == $key ? ' selected="selected"' : '' ) . '>' . $val . '</option>';
        }

        echo '
			</select>
			<select id="sort_ad" name="sort_ad">
				<option value="ASC">по возрастанию</option>
				<option value="DESC"' . ( $sort_ad == 'DESC' ? ' selected="selected"' : '' ) . '>по убыванию</option>
			</select>
		</div>

		<div class="form-submit">
			<input type="submit" value="Показать" />
		</div>
		
	</form>';

        $query = "
		SELECT
			COUNT( * )
		FROM
			`" . $db_config[ 'pref' ] . "shipments` AS `s`
		INNER JOIN `" . $db_config[ 'pref' ] . "accounts` AS `a`
         ON `a`.`account_id` = `s`.`account_id`
         AND `a`.`is_active` = 1";

        if ( ! empty( $service_code ) )
        {

            $query .= "
      INNER JOIN `" . $db_config[ 'pref' ] . "shipment_services` AS `ss`
         ON `ss`.`shipment_id` = `s`.`shipment_id`
         AND `ss`.`is_active` = 1
         AND `ss`.`is_main` = 1
         AND `ss`.`service_code` IN ( '" . implode( "','", array_map( 'addslashes', $service_code ) ) . "' )";
        }

        if ( ! empty( $add_service_code ) )
        {
            $query .= "
      INNER JOIN `" . $db_config[ 'pref' ] . "shipment_services` AS `ss`
         ON `ss`.`shipment_id` = `s`.`shipment_id`
         AND `ss`.`is_active` = 1
         AND `ss`.`is_main` = 0
         AND `ss`.`service_code` IN ( '" . implode( "','", array_map( 'addslashes', $add_service_code ) ) . "' )";
        }

        $query .= "
		WHERE
			1 = 1";

        if ( ! empty( $account ) )
            $query .= "
			AND `a`.`account_name` = '" . $db->real_escape_string( $account ) . "'";

        if ( ! empty( $number ) )
            $query .= "
			AND `s`.`shipment_number` = '" . $db->real_escape_string( $number ) . "'";

        if ( ! empty( $barcode ) )
            $query .= "
			AND `s`.`shipment_barcode` = '" . $db->real_escape_string( $barcode ) . "'";

        if ( ! empty( $vendor_fm_code ) )
            $query .= "
			AND `s`.`vendor_fm_code` = '" . $db->real_escape_string( $vendor_fm_code ) . "'";

        if ( ! empty( $vendor_lm_code ) )
            $query .= "
			AND `s`.`vendor_lm_code` = '" . $db->real_escape_string( $vendor_lm_code ) . "'";

        /*
        if ( !empty( $receiver_firstname ) )
            $query .= "
                AND `sh`.`receiver_firstname` = '" . $db->real_escape_string( $receiver_firstname ) . "'";

        if ( !empty( $receiver_middlename ) )
            $query .= "
                AND `sh`.`receiver_middlename` = '" . $db->real_escape_string( $receiver_middlename ) . "'";

        if ( !empty( $receiver_lastname ) )
            $query .= "
                AND `sh`.`receiver_lastname` = '" . $db->real_escape_string( $receiver_lastname ) . "'";
        */

        if ( ! empty( $period_from ) )
            $query .= "
			AND DATE( `s`.`date_create` ) >= '" . $db->real_escape_string( $period_from ) . "'";

        if ( ! empty( $period_to ) )
            $query .= "
			AND DATE( `s`.`date_create` ) <= '" . $db->real_escape_string( $period_to ) . "'";

        $result = $db->query( $query ) or die( $db->last_error() );
        $count = $db->result( $result );
        $db->free_result( $result );

        if ( $count != 0 )
        {

            $pages = LIMIT_PER_PAGE > 0 ? ceil( $count / LIMIT_PER_PAGE ) : 1;

            $query = "
			SELECT
				`s`.*,
				`a`.`account_name`
			FROM
				`" . $db_config[ 'pref' ] . "shipments` AS `s`
			INNER JOIN `" . $db_config[ 'pref' ] . "accounts` AS `a`
				ON `a`.`account_id` = `s`.`account_id`";

            if ( ! empty( $service_code ) )
            {
                $query .= "
		   INNER JOIN `" . $db_config[ 'pref' ] . "shipment_services` AS `ss`
		      ON `ss`.`shipment_id` = `s`.`shipment_id`
		      AND `ss`.`is_active` = 1
		      AND `ss`.`is_main` = 1
		      AND `ss`.`service_code` IN ( '" . implode( "','", array_map( 'addslashes', $service_code ) ) . "' )";
            }

            if ( ! empty( $add_service_code ) )
            {
                $query .= "
		  INNER JOIN `" . $db_config[ 'pref' ] . "shipment_services` AS `ss`
			 ON `ss`.`shipment_id` = `s`.`shipment_id`
			 AND `ss`.`is_active` = 1
			 AND `ss`.`is_main` = 0
			 AND `ss`.`service_code` IN ( '" . implode( "','", array_map( 'addslashes', $add_service_code ) ) . "' )";
            }

            $query .= "
			WHERE
				   1 = 1";

            if ( ! empty( $account ) )
                $query .= "
				AND `a`.`account_name` = '" . $db->real_escape_string( $account ) . "'";

            if ( ! empty( $number ) )
                $query .= "
				AND `s`.`shipment_number` = '" . $db->real_escape_string( $number ) . "'";

            if ( ! empty( $barcode ) )
                $query .= "
				AND `s`.`shipment_barcode` = '" . $db->real_escape_string( $barcode ) . "'";

            if ( ! empty( $vendor_fm_code ) )
                $query .= "
				AND `s`.`vendor_fm_code` = '" . $db->real_escape_string( $vendor_fm_code ) . "'";

            if ( ! empty( $vendor_lm_code ) )
                $query .= "
				AND `s`.`vendor_lm_code` = '" . $db->real_escape_string( $vendor_lm_code ) . "'";

            /*
            if ( !empty( $receiver_firstname ) )
                $query .= "
                    AND `sh`.`receiver_firstname` = '" . $db->real_escape_string( $receiver_firstname ) . "'";

            if ( !empty( $receiver_middlename ) )
                $query .= "
                    AND `sh`.`receiver_middlename` = '" . $db->real_escape_string( $receiver_middlename ) . "'";

            if ( !empty( $receiver_lastname ) )
                $query .= "
                    AND `sh`.`receiver_lastname` = '" . $db->real_escape_string( $receiver_lastname ) . "'";
            */

            if ( ! empty( $period_from ) )
                $query .= "
				AND DATE( `s`.`date_create` ) >= '" . $db->real_escape_string( $period_from ) . "'";

            if ( ! empty( $period_to ) )
                $query .= "
				AND DATE( `s`.`date_create` ) <= '" . $db->real_escape_string( $period_to ) . "'";

            $query .= "
			ORDER BY `s`.`" . $sort . "` " . $sort_ad . "
			LIMIT " . LIMIT_FROM . ", " . LIMIT_PER_PAGE;

            $result = $db->query( $query ) or die( $db->last_error() );
            if ( $db->num_rows( $result ) != 0 )
            {

                echo '
			<div style="text-align: right; margin: 10px 0;">
				Найдено: ' . $count . '
			</div>
			<table class="info" id="result">
				<thead>
				   <th>
				      <input type="checkbox" data-check="1" data-bill data-shipment />
				   </th>
					<th>
						Дата создания \ Аккаунт
					</th>
					<th>
						Номер отправления \ штрихкод
					</th>
					<th>
						Основная услуга
					</th>
					<th>
						Доп. услуги
					</th>
					<th>
						Накладная
					</th>
					<th>
						Доставка \ ПВЗ
					</th>
					<th width="16">
						*
					</th>
				</thead>
				<tbody>';
                while ( $shipment = $db->fetch_assoc( $result ) )
                {

                    echo '
					<tr>
					   <th>
					      <input type="checkbox" data-group="1" data-shipment="' . (int) $shipment[ 'shipment_id' ] . '" />
					   </th>
						<td>
							' . $shipment[ 'date_create' ] . '<br />
							<a href="?show[]=settings&show[]=accounts&id=' . (int) $shipment[ 'account_id' ] . '">' . $shipment[ 'account_name' ] . '</a>
						</td>
						<td>
							<a href="?show[]=shipments&id=' . (int) $shipment[ 'shipment_id' ] . '">' . $shipment[ 'shipment_number' ] . '</a><br />
							' . $shipment[ 'shipment_barcode' ] . '
						</td>
						<td>';

                    $q = "
							SELECT 
								*
							FROM
								`" . $db_config[ 'pref' ] . "shipment_services` AS `ss`
							WHERE
								`ss`.`shipment_id` = " . (int) $shipment[ 'shipment_id' ] . "
								AND `ss`.`is_active` = 1
								AND `is_main` = 1";
                    $res = $db->query( $q ) or die( $db->last_error() );
                    $service = $db->fetch_assoc( $res );
                    $db->free_result( $res );

                    echo '
						<a href="?show[]=settings&show[]=services&code=' . $service[ 'service_code' ] . '">
							' . $service[ 'service_name' ] . '
						</a>';

                    echo '
						</td>
						<td>';

                    $q = "
							SELECT 
								*
							FROM
								`" . $db_config[ 'pref' ] . "shipment_services` AS `ss`
							WHERE
								`ss`.`shipment_id` = " . (int) $shipment[ 'shipment_id' ] . "
								AND `ss`.`is_active` = 1
								AND `is_main` = 0";
                    $res = $db->query( $q ) or die( $db->last_error() );
                    while ( $service = $db->fetch_assoc( $res ) )
                    {
                        echo '
							<div>
								<a href="?show[]=settings&show[]=services&code=' . $service[ 'service_code' ] . '">
									' . $service[ 'service_name' ] . '
								</a>
							</div>';
                    }
                    $db->free_result( $res );

                    echo '
						</td>
						<td align="center">
							<a href="http://lcm-express.com/waybill/' . htmlspecialchars( $shipment[ 'shipment_barcode' ] ) . '&pages=1" target="_blank">
								<span class="fa fa-floppy-o fa-2x" aria-hidden="true"></span>
							</a>
						</td>
						<td>
							<a href="?show[]=settings&show[]=deliveries&code=' . $shipment[ 'delivery_from_code' ] . '">' . $shipment[ 'delivery_from_code' ] . '<a/>
							- 
							<a href="?show[]=settings&show[]=deliveries&code=' . $shipment[ 'delivery_to_code' ] . '">' . $shipment[ 'delivery_to_code' ] . '<a/><br />';

                    if ( $shipment[ 'delivery_to_code' ] == 'term' )
                    {
                        $q = "
									SELECT 
										*
									FROM
										`" . $db_config[ 'pref' ] . "terminals` AS `t`
									WHERE
										`t`.`terminal_id` = " . (int) $shipment[ 'terminal_to_id' ] . "
										AND `t`.`is_active` = 1";
                        $terminal = $db->fetch_assoc( $res );
                        $db->free_result( $res );
                        echo $terminal[ 'address' ];
                    }

                    echo '
						</td>
						<th>
							' . ( $shipment[ 'is_active' ] ? '<img src="img/icon_ok_16.png" title="Активен" />' : '<img src="img/icon_no_16.png" title="Отменен" />' ) . '
						</th>
					</tr>';
                }
                $db->free_result( $result );
                echo '
				</tbody>
			</table>';

            } else echo '<div class="not_found">Ничего не найдено</div>';

            getPages( $pages );

            echo '
		<h2>Выгрузка (Export)</h2>    
            
         <table class="info">
            <tr>
               <th colspan="2">
                  <button type="button" id="export">Экспорт</button>
				  <button type="button" id="invoices-export">Счета</button>
               </th>
            </tr>
         </table>
		
		<div class="show-checked">
      
         <div class="form-title">
            Доступные действия
         </div>
      
         <h2>Сменить статус для выбранных</h2>
			
		   <form action="" method="post">
						
			   <input type="hidden" name="action" value="change_status" />
			   <input type="hidden" name="shipments" value="" />
			
			   <table class="info">
				  <tr>
			         <th>
			            Дата статуса
			         </th>
			         <td>
			            <input type="text" name="datetime" value="" class="datepicker" />
			         </td>
			      </tr>
			      <tr>
					  <th>
						 Статус
					  </th>
					  <td>
						 <select name="status_code">
							<option value=""> - выберите из списка - </option>';

            $q = "
							SELECT
								*
							FROM
								`" . $db_config[ 'pref' ] . "statuses` AS `s`
							WHERE
								`s`.`lang_code` = 'ru'
								AND `s`.`is_active` = 1";
            $res = $db->query( $q ) or die( $db->last_error() );
            while ( $status = $db->fetch_assoc( $res ) )
            {
                echo '
							<option value="' . $status[ 'status_code' ] . '">' . $status[ 'status_text' ] . '</option>';
            }

            echo '
						 </select>
					  </td>
				   </tr>
			      <tr>
			         <th>
			            Местоположение
			         </th>
			         <td>
			            <input type="text" name="location" value="" />
			         </td>
			      </tr>
			      <tr>
			         <th colspan="2">
			            <button type="submit">Сменить</button>
			         </th>
			      </tr>
			   </table>
			
		   </form>
		
		   <h2>Действия над выбранными</h2>    
            
         <table class="info">
            <tr>
               <th colspan="2">
                  <button type="button" id="opinter-export">Опинтер XML</button>
                  <button type="button" id="lcm-export">ЛСМ-Брокер XML</button>
               </th>
            </tr>
         </table>
         
      </div>
	  
	  <ul class="links">
		  <li>
			<form action="" method="post" enctype="multipart/form-data" class="submit-loading">
				  <input type="hidden" name="action" value="import" />
				  <input type="file" name="files[]" multiple accept=".' . implode( ',.', $_files ) . '" />
				  <button type="submit">Обновить</button>
			   </form>
		  </li>
	  </ul>
	  
	  <div id="export-data" class="modal" style="display: none;">
		<div class="modal-content">
		
			<h3>Входные параметры для экспорта</h3>
			
			<form action="" method="post" id="export-form">
               <input type="hidden" name="action" value="export" />
               <input type="hidden" name="shipments" value="" />
			   <h2 id="export-info" style="display: none;"></h2>
               <table class="info">
                  <tr>
                     <th>
                        Аккаунт
                     </th>
                     <td>
                        <input type="text" name="account" value="' . $account . '" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Период от
                     </th>
                     <td>
                        <input type="text" name="period_from" value="' . $period_from . '" class="datepicker" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Период до
                     </th>
                     <td>
                        <input type="text" name="period_to" value="' . $period_to . '" class="datepicker" />
                     </td>
                  </tr>
               </table>
			   <button type="button" class="modal-close">Отмена</button>
               <button type="submit">Сформировать</button>
            </form>
			
		</div>
	  </div>
	  
	  <div id="invoices-data" class="modal" style="display: none;">
		<div class="modal-content">
		
			<h3>Входные параметры для экспорта счетов</h3>
			
			<form action="" method="post" id="invoices-form">
               <input type="hidden" name="action" value="invoices" />
               <input type="hidden" name="shipments" value="" />
			   <h2 id="invoices-info" style="display: none;"></h2>
               <table class="info">
               	<tr>
                     <th>
                        Аккаунт
                     </th>
                     <td>
                        <input type="text" name="account" value="' . $account . '" />
                     </td>
                  </tr>
               	<tr>
                     <th>
                        Тип счета
                     </th>
                     <td>
                        <select name="invoice_type_code">
                        	<option value=""> -- все -- </option>
                        	<option value="main">main</option>
                        	<option value="correct">correct</option>
                        </select>
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Дата счета (от)
                     </th>
                     <td>
                        <input type="text" name="period_from" value="' . $period_from . '" class="datepicker" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Дата счета (до)
                     </th>
                     <td>
                        <input type="text" name="period_to" value="' . $period_to . '" class="datepicker" />
                     </td>
                  </tr>
               </table>
			   <button type="button" class="modal-close">Отмена</button>
			   <button type="submit">Сформировать</button>
            </form>
			
		</div>
	  </div>
      
      <div id="opinter-data" class="modal" style="display: none;">
         <div class="modal-content">
         
            <h3>Входные параметры для XML-документа</h3>';

            $opinter = json_decode( file_get_contents( DIR_MANAGER . '/opinter.json' ) );

            echo '
            <form action="" method="post" id="opinter-form">
               <input type="hidden" name="action" value="opinter" />
               <input type="hidden" name="shipments" value="" />
               <table class="info">
                  <tr>
                     <th>
                        Номер авианакладной
                     </th>
                     <td>
                        <input type="text" name="AVIANUM" value="' . htmlspecialchars( $opinter->AVIANUM ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Дата прилета борта
                     </th>
                     <td>
                        <input type="text" name="ARRIVEDATE" class="datepicker" value="' . htmlspecialchars( $opinter->ARRIVEDATE ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Код страна-продавца
                     </th>
                     <td>
                        <input type="text" name="DELIVERYTERMS_TRADINGCOUNTRYCODE" value="' . htmlspecialchars( $opinter->DELIVERYTERMS_TRADINGCOUNTRYCODE ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Код страна-отправления
                     </th>
                     <td>
                        <input type="text" name="DELIVERYTERMS_DISPATCHCOUNTRYCODE" value="' . htmlspecialchars( $opinter->DELIVERYTERMS_DISPATCHCOUNTRYCODE ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Условия поставки
                     </th>
                     <td>
                        <input type="text" name="DELIVERYTERMS_DELIVERYTERMSSTRINGCODE" value="' . htmlspecialchars( $opinter->DELIVERYTERMS_DELIVERYTERMSSTRINGCODE ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Код аэропорта вылета
                     </th>
                     <td>
                        <input type="text" name="DEPARTUEPOINT_IATACODE" value="' . htmlspecialchars( $opinter->DEPARTUEPOINT_IATACODE ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Код аэропорта прилета
                     </th>
                     <td>
                        <input type="text" name="DELIVERYPOINT_IATACODE" value="' . htmlspecialchars( $opinter->DELIVERYPOINT_IATACODE ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th colspan="2" style="text-align: right;">
                        <button type="button" class="modal-close">Отмена</button>
                        <button type="submit">Сформировать</button>
                     </th>
                  </tr>
               </table>
            </form>
         </div>
      </div>
      
      <div id="lcm-data" class="modal" style="display: none;">
         <div class="modal-content">
         
            <h3>Входные параметры для XML-документа</h3>';

            $lcm = json_decode( file_get_contents( DIR_MANAGER . '/lcm.json' ) );

            echo '
            <form action="" method="post" id="lcm-form">
               <input type="hidden" name="action" value="lcmbroker" />
               <input type="hidden" name="shipments" value="" />
               <table class="info">
                  <tr>
                     <th>
                        Тип декларации
                     </th>
                     <td>
                        <input type="text" name="EnterOrExitCustomsTerritory" value="' . htmlspecialchars( $lcm->EnterOrExitCustomsTerritory ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Цифровой код док-та
                     </th>
                     <td>
                        <input type="text" name="DeclarantPerson_PersonIdCard_IdentityCardCode" value="' . htmlspecialchars( $lcm->DeclarantPerson_PersonIdCard_IdentityCardCode ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Наименование док-та
                     </th>
                     <td>
                        <input type="text" name="DeclarantPerson_PersonIdCard_IdentityCardName" value="' . htmlspecialchars( $lcm->DeclarantPerson_PersonIdCard_IdentityCardName ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Способ разм. инф-ции
                     </th>
                     <td>
                        <input type="text" name="Goods_PassDeclGoodsInfo_MethodPostingInfo" value="' . htmlspecialchars( $lcm->Goods_PassDeclGoodsInfo_MethodPostingInfo ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Цифровой код ДЕИ
                     </th>
                     <td>
                        <input type="text" name="GoodsQuantity_MeasureUnitOperationCode" value="' . htmlspecialchars( $lcm->GoodsQuantity_MeasureUnitOperationCode ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        ДЕИ
                     </th>
                     <td>
                        <input type="text" name="GoodsQuantity_MeasureUnitOperation" value="' . htmlspecialchars( $lcm->GoodsQuantity_MeasureUnitOperation ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Цифровой код валюты
                     </th>
                     <td>
                        <input type="text" name="GoodsCost_CurrencyCode" value="' . htmlspecialchars( $lcm->GoodsCost_CurrencyCode ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Валюта
                     </th>
                     <td>
                        <input type="text" name="GoodsCost_CurrencyName" value="' . htmlspecialchars( $lcm->GoodsCost_CurrencyName ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Язык заполнения декл.
                     </th>
                     <td>
                        <input type="text" name="LanguagePassDecl" value="' . htmlspecialchars( $lcm->LanguagePassDecl ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Фамилия представителя
                     </th>
                     <td>
                        <input type="text" name="Signer_PersonSurname" value="' . htmlspecialchars( $lcm->Signer_PersonSurname ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Имя представителя
                     </th>
                     <td>
                        <input type="text" name="Signer_PersonName" value="' . htmlspecialchars( $lcm->Signer_PersonName ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Отчество представителя
                     </th>
                     <td>
                        <input type="text" name="Signer_PersonMiddleName" value="' . htmlspecialchars( $lcm->Signer_PersonMiddleName ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Полномочия представителя
                     </th>
                     <td>
                        <input type="text" name="Signer_PersonPost" value="' . htmlspecialchars( $lcm->Signer_PersonPost ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Подпись лица
                     </th>
                     <td>
                        <input type="text" name="Signer_PersonMode" value="' . htmlspecialchars( $lcm->Signer_PersonMode ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Дата заполнения
                     </th>
                     <td>
                        <input type="text" name="Signer_IssueDate" value="' . htmlspecialchars( $lcm->Signer_IssueDate ) . '" required="required" class="datepicker" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Наименование док-та
                     </th>
                     <td>
                        <input type="text" name="Signer_CustomsRepresCertificate_PrDocumentName" value="' . htmlspecialchars( $lcm->Signer_CustomsRepresCertificate_PrDocumentName ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Номер док-та
                     </th>
                     <td>
                        <input type="text" name="Signer_CustomsRepresCertificate_PrDocumentNumber" value="' . htmlspecialchars( $lcm->Signer_CustomsRepresCertificate_PrDocumentNumber ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Наименование дог-ра
                     </th>
                     <td>
                        <input type="text" name="Signer_ContractRepresDecl_PrDocumentName" value="' . htmlspecialchars( $lcm->Signer_ContractRepresDecl_PrDocumentName ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Номер дог-ра
                     </th>
                     <td>
                        <input type="text" name="Signer_ContractRepresDecl_PrDocumentNumber" value="' . htmlspecialchars( $lcm->Signer_ContractRepresDecl_PrDocumentNumber ) . '" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th>
                        Дата дог-ра
                     </th>
                     <td>
                        <input type="text" name="Signer_ContractRepresDecl_PrDocumentDate" value="' . htmlspecialchars( $lcm->Signer_ContractRepresDecl_PrDocumentDate ) . '" class="datepicker" required="required" />
                     </td>
                  </tr>
                  <tr>
                     <th colspan="2" style="text-align: right;">
                        <button type="button" class="modal-close">Отмена</button>
                        <button type="submit">Сформировать</button>
                     </th>
                  </tr>
               </table>
            </form>
         </div>
      </div>';

        } else echo '<div class="not_found">Ничего не найдено</div>';

}
