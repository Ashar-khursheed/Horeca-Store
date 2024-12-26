<?php


namespace Botble\Ecommerce\Forms;

use Botble\Base\Forms\FormAbstract;
use Botble\Ecommerce\Models\EliteShipment;
use Botble\Ecommerce\Forms\Concerns\HasSubmitButton;

class EliteShipmentForm extends FormAbstract
{
	use HasSubmitButton;

	public function setup(): void
	{
		$this
		->setupModel($this->getModel() ?? new EliteShipment())
		->contentOnly()
		->add('shipper_name', 'text', [
			'label' => 'Shipper Name:',
			'attr' => [
				'placeholder' => 'Enter shipper name',
			],
		])
		->add('shipper_address', 'text', [
			'label' => 'Shipper Address:',
			'attr' => [
				'placeholder' => 'Enter shipper address',
			],
		])
		->add('shipper_area', 'text', [
			'label' => 'Shipper Area:',
			'attr' => [
				'placeholder' => 'Enter shipper area',
			],
		])
		->add('shipper_city', 'text', [
			'label' => 'Shipper City:',
			'attr' => [
				'placeholder' => 'Enter shipper city',
			],
		])
		->add('shipper_telephone', 'text', [
			'label' => 'Shipper Telephone:',
			'attr' => [
				'placeholder' => 'Enter shipper telephone',
			],
		])
		->add('receiver_name', 'text', [
			'label' => 'Receiver Name:',
			'attr' => [
				'placeholder' => 'Enter receiver name',
			],
			'value' => $this->getModel()->order->shippingAddress->name,
		])
		->add('receiver_address', 'text', [
			'label' => 'Receiver Address:',
			'attr' => [
				'placeholder' => 'Enter receiver address',
			],
			'value' => $this->getModel()->order->shippingAddress->address,
		])
		->add('receiver_address2', 'text', [
			'label' => 'Receiver Address 2:',
			'attr' => [
				'placeholder' => 'Enter additional receiver address (optional)',
			],
			'default_value' => '',
		])
		->add('receiver_area', 'text', [
			'label' => 'Receiver Area:',
			'attr' => [
				'placeholder' => 'Enter receiver area',
			],
			'value' => $this->getModel()->order->shippingAddress->state,
		])
		->add('receiver_city', 'text', [
			'label' => 'Receiver City:',
			'attr' => [
				'placeholder' => 'Enter receiver city',
			],
			'value' => strtoupper($this->getModel()->order->shippingAddress->city),
		])
		->add('receiver_telephone', 'text', [
			'label' => 'Receiver Telephone:',
			'attr' => [
				'placeholder' => 'Enter receiver telephone',
			],
			'value' => $this->getModel()->order->shippingAddress->phone,
		])
		->add('receiver_mobile', 'text', [
			'label' => 'Receiver Mobile:',
			'attr' => [
				'placeholder' => 'Enter receiver mobile',
			],
			'value' => $this->getModel()->order->shippingAddress->phone,
		])
		->add('receiver_email', 'email', [
			'label' => 'Receiver Email:',
			'attr' => [
				'placeholder' => 'Enter receiver email',
			],
			'value' => $this->getModel()->order->shippingAddress->email,
		])
		->add('shipping_reference', 'text', [
			'label' => 'Shipping Reference:',
			'attr' => [
				'placeholder' => 'Enter shipping reference',
			],
			'value' => str_pad(mt_rand(1,99999999),8,'0',STR_PAD_LEFT),
		])
		->add('orders', 'text', [
			'label' => 'Orders:',
			'attr' => [
				'placeholder' => 'Enter order details',
				'readonly' => true
			],
			'value' => $this->getModel()->order->code,
		])
		->add('item_type', 'text', [
			'label' => 'Item Type:',
			'attr' => [
				'placeholder' => 'Enter item type',
				'readonly' => true
			],
			'value' => 'X',
		])
		->add('item_description', 'text', [
			'label' => 'Item Description:',
			'attr' => [
				'placeholder' => 'Enter item description',
			],
		])
		->add('item_value', 'number', [
			'label' => 'Item Value:',
			'attr' => [
				'placeholder' => 'Enter item value',
			],
			'value' => $this->getModel()->order->amount,
		])
		->add('dangerousGoodsType', 'text', [
			'label' => 'Dangerous Goods Type:',
			'attr' => [
				'placeholder' => 'Enter dangerous goods type',
			],
		])
		->add('weight_kg', 'number', [
			'label' => 'Weight (kg):',
			'attr' => [
				'placeholder' => 'Enter weight in kg',
			],
			'value' => $this->getModel()->order->products->sum('qty'),
		])
		->add('no_of_pieces', 'number', [
			'label' => 'No of Pieces:',
			'attr' => [
				'placeholder' => 'Enter number of pieces',
			],
			'value' => $this->getModel()->order->products->sum('qty'),
		])
		->add('service_type', 'text', [
			'label' => 'Service Type:',
			'attr' => [
				'placeholder' => 'Enter service type',
				'readonly' => true
			],
			'value' => 'N',
		])
		->add('cod_value', 'number', [
			'label' => 'COD Value:',
			'attr' => [
				'placeholder' => 'Enter COD value',
			],
			'value' => $this->getModel()->order->shipping_amount,
		])
		->add('service_date', 'date', [
			'label' => 'Service Date:',
			'attr' => [
				'placeholder' => 'Enter service date (YYYY-MM-DD)',
			],
		])
		->add('service_time', 'time', [
			'label' => 'Service Time:',
			'attr' => [
				'placeholder' => 'Enter service time (e.g., 10:00-18:00)',
			],
		])
		->add('created_by', 'text', [
			'label' => 'Created By:',
			'attr' => [
				'placeholder' => 'Enter creator name',
			],
		])
		->add('special', 'text', [
			'label' => 'Special Instructions:',
			'attr' => [
				'placeholder' => 'Enter any special instructions (optional)',
			],
			'default_value' => '',
		])
		->add('order_type', 'text', [
			'label' => 'Order Type:',
			'attr' => [
				'placeholder' => 'Enter order type',
				'readonly' => true
			],
			'value' => 'D'
		])
		->add('ship_region', 'text', [
			'label' => 'Ship Region:',
			'attr' => [
				'placeholder' => 'Enter ship region (e.g., AE)',
				'readonly' => true
			],
			'value' => 'AE',
		]);
	}
}
