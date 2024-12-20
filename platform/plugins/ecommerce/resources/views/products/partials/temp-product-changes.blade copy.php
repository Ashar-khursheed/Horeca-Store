@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Edit Temp Products</title>

	<!-- Bootstrap CSS -->
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">

	<!-- Custom CSS (Optional) -->
	<style>
		.edit-icon {
			cursor: pointer;
			font-size: 18px;
		}
	</style>
</head>
<body>
	<ul class="nav nav-tabs" id="myTab" role="tablist">
		<li class="nav-item" role="presentation">
			<button class="nav-link active" id="pricing-tab" data-bs-toggle="tab" data-bs-target="#pricing" type="button" role="tab" aria-controls="pricing" aria-selected="true">Pricing</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="content_writer-tab" data-bs-toggle="tab" data-bs-target="#content_writer" type="button" role="tab" aria-controls="content_writer" aria-selected="false">Content Writer</button>
		</li>
		<li class="nav-item" role="presentation">
			<button class="nav-link" id="graphics-tab" data-bs-toggle="tab" data-bs-target="#graphics" type="button" role="tab" aria-controls="graphics" aria-selected="false">Graphics</button>
		</li>
	</ul>
	<div class="tab-content" id="myTabContent">

		<div class="tab-pane fade show active" id="pricing" role="tabpanel" aria-labelledby="pricing-tab">
			<div class="container mt-1">
				<div class="row">
					<div class="col-md-3 mb-3">
						<label class="form-label bg-info text-white text-center py-3 h6">Content In Progress<br/><span class="h2">{{ $tempPricingProducts->where('approval_status', 'in-process')->count() }}</span></label>
					</div>
					<div class="col-md-3 mb-3">
						<label class="form-label bg-warning text-white text-center py-3 h6">Submitted for Approval<br/><span class="h2">{{ $tempPricingProducts->where('approval_status', 'pending')->count() }}</span></label>
					</div>
					<div class="col-md-3 mb-3">
						<label class="form-label bg-success text-white text-center py-3 h6">Ready to Publish<br/><span class="h2">{{ $tempPricingProducts->where('approval_status', 'approved')->count() }}</span></label>
					</div>
					<div class="col-md-3 mb-3">
						<label class="form-label bg-danger text-white text-center py-3 h6">Rejected for Corrections<br/><span class="h2">{{ $tempPricingProducts->sum('rejection_count') }}</span></label>
					</div>
				</div>
				<form action="{{ route('temp-products.approve') }}" method="POST">
					@csrf
					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Product ID</th>
									<th>Product Name</th>
									<th>SKU</th>
									<th>Price</th>
									<th>Sale Price</th>
									{{-- <th>Current Status</th> --}}
									<th>Approval Status</th>
									<th>Edit</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($tempPricingProducts as $tempPricingProduct)
									@if($tempPricingProduct->approval_status == 'pending')
										<tr id="product-row-{{ $tempPricingProduct->id }}">
											<td>{{ $tempPricingProduct->product_id }}</td>
											<td class="product-name">{{ $tempPricingProduct->name }}</td>
											<td class="product-description">{{ $tempPricingProduct->sku }}</td>
											<td class="product-description">{{ $tempPricingProduct->price }}</td>
											<td class="product-description">{{ $tempPricingProduct->sale_price }}</td>
											<td class="product-description">{{ $approvalStatuses[$tempPricingProduct->approval_status] ?? '' }}</td>
											{{-- <td class="product-status">{{ $tempPricingProduct->status }}</td> --}}
											{{-- <td>
												<select name="approval_status[{{ $tempPricingProduct->id }}]" class="form-control approval-status-dropdown">
													<option value="pending" {{ $tempPricingProduct->approval_status == 'pending' ? 'selected' : '' }}>Pending</option>
													<option value="approved" {{ $tempPricingProduct->approval_status == 'approved' ? 'selected' : '' }}>Approved</option>
													<option value="rejected" {{ $tempPricingProduct->approval_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
												</select>
											</td> --}}
											<td>
												<button type="button" id="edit_pricing_modal" data-toggle="modal" data-target="#editPricingModal" data-product="{{ htmlspecialchars(json_encode($tempPricingProduct->toArray(), JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') }}">
													<i class="fas fa-pencil-alt"></i>
												</button>
											</td>
										</tr>
									@endif
								@endforeach
							</tbody>
						</table>
					</div>
				</form>
			</div>
		</div>

		<div class="tab-pane fade" id="content_writer" role="tabpanel" aria-labelledby="content_writer-tab">
			<div class="container mt-5">
				<form action="{{ route('temp-products.approve') }}" method="POST">
					@csrf
					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Product ID</th>
									<th>Product Name</th>
									<th>Change Description</th>
									<th>Current Status</th>
									<th>Approval Status</th>
									<th>Edit</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($tempContentProducts as $tempContentProduct)
								<tr id="product-row-{{ $tempContentProduct->id }}">
									<td>{{ $tempContentProduct->product_id }}</td>
									<td class="product-name">{{ $tempContentProduct->name }}</td>
									<td class="product-description">{{ $tempContentProduct->description }}</td>
									<td class="product-status">{{ $tempContentProduct->status }}</td>
									<td>
										<select name="approval_status[{{ $tempContentProduct->id }}]" class="form-control approval-status-dropdown">
											<option value="pending" {{ $tempContentProduct->approval_status == 'pending' ? 'selected' : '' }}>Pending</option>
											<option value="approved" {{ $tempContentProduct->approval_status == 'approved' ? 'selected' : '' }}>Approved</option>
											<option value="rejected" {{ $tempContentProduct->approval_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
										</select>
									</td>
									<td>
										<button type="button" class="edit-icon" data-toggle="modal" data-target="#editContentModal"
										data-id="{{ $tempContentProduct->id }}"
										data-name="{{ $tempContentProduct->name }}"
										data-description="{{ $tempContentProduct->description }}"
										data-content="{{ $tempContentProduct->content }}"
										data-status="{{ $tempContentProduct->status }}"
										data-approval-status="{{ $tempContentProduct->approval_status }}"></button>
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>

					<button type="submit" class="btn btn-success" id="save-changes-btn">Save Approval Changes</button>
				</form>
			</div>
		</div>

		<div class="tab-pane fade" id="graphics" role="tabpanel" aria-labelledby="graphics-tab">
			<div class="container mt-5">
				<form action="{{ route('temp-products.approve') }}" method="POST">
					@csrf
					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
								<tr>
									<th>Product ID</th>
									<th>Product Name</th>
									<th>Change Description</th>
									<th>Current Status</th>
									<th>Approval Status</th>
									<th>Edit</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($tempGraphicsProducts as $tempGraphicsProduct)
								<tr id="product-row-{{ $tempGraphicsProduct->id }}">
									<td>{{ $tempGraphicsProduct->product_id }}</td>
									<td class="product-name">{{ $tempGraphicsProduct->name }}</td>
									<td class="product-description">{{ $tempGraphicsProduct->description }}</td>
									<td class="product-status">{{ $tempGraphicsProduct->status }}</td>
									<td>
										<select name="approval_status[{{ $tempGraphicsProduct->id }}]" class="form-control approval-status-dropdown">
											<option value="pending" {{ $tempGraphicsProduct->approval_status == 'pending' ? 'selected' : '' }}>Pending</option>
											<option value="approved" {{ $tempGraphicsProduct->approval_status == 'approved' ? 'selected' : '' }}>Approved</option>
											<option value="rejected" {{ $tempGraphicsProduct->approval_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
										</select>
									</td>
									<td>
										<button type="button" class="edit-icon" data-toggle="modal" data-target="#editProductModal"
										data-id="{{ $tempGraphicsProduct->id }}"
										data-name="{{ $tempGraphicsProduct->name }}"
										data-description="{{ $tempGraphicsProduct->description }}"
										data-content="{{ $tempGraphicsProduct->content }}"
										data-status="{{ $tempGraphicsProduct->status }}"
										data-approval-status="{{ $tempGraphicsProduct->approval_status }}"></button>
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
					</div>

					<button type="submit" class="btn btn-success" id="save-changes-btn">Save Approval Changes</button>
				</form>
			</div>
		</div>
	</div>

	<!-- Pricing Modal -->
	<div class="modal fade" id="editPricingModal" tabindex="-1" role="dialog" aria-labelledby="editPricingModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="editPricingModalLabel">Edit Product</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="{{ route('temp-products.admin_pricing_approve') }}" method="POST">
						@csrf
						<div class="product-card">
							<div class="product-header">
								<h6>Product ID: <span id="pricing_temp_header_id"></span></h6>
								<h4 id="pricing_temp_header_name"></h4>
								<input type="hidden" id="pricing_temp_id" name="id">
							</div>
							<div class="row">
								<div class="mb-3 col-md-6">
									<label for="sku" class="form-label">SKU</label>
									<input type="text" class="form-control" id="pricing_sku" name="sku">
								</div>

								<div class="mb-3 col-md-6">
									<label for="price" class="form-label">Price</label>
									<input type="number" step="0.01" class="form-control" id="pricing_price" name="price" onchange="calculateMargin()">
								</div>

								<div class="mb-3 col-md-6">
									<div class="d-flex justify-content-between">
										<label for="priceSale" class="form-label">Price After Discount</label>
										<a href="javascript:void(0)" id="chooseDiscountPeriod">Choose Discount Period</a>
									</div>

									<input type="number" step="0.01" class="form-control me-2" id="pricing_sale_price" name="sale_price" onchange="calculateMargin()">
								</div>

								<div class="col-md-6 mb-3">
									<label class="form-label">Unit of Measurement</label>
									<select id="pricing_unit_of_measurement_id" name="unit_of_measurement_id" class="form-control">
										<option value="">Select a unit</option>
										@foreach($unitOfMeasurements as $id => $name)
										<option value="{{ $id }}">{{ $name }}</option>
										@endforeach
									</select>
								</div>
							</div>
							<div id="discountPeriodFields" class="d-none">
								<div class="row mb-3">
									<div class="col">
										<label for="fromDate" class="form-label">From Date</label>
										<input type="date" class="form-control" id="pricing_from_date" name="from_date">
									</div>
									<div class="col">
										<label for="toDate" class="form-label">To Date</label>
										<input type="date" class="form-control" id="pricing_to_date" name="to_date">
									</div>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6 mb-3">
									<label for="costPerItem" class="form-label">Cost per Item</label>
									<input type="number" step="0.01" class="form-control" id="pricing_cost_per_item" name="cost_per_item" placeholder="Enter cost per item" onchange="calculateMargin()">
								</div>
								<div class="col-md-6 mb-3">
									<label for="costPerItem" class="form-label">Margin (%)</label>
									<input type="text" class="form-control" id="pricing_margin" name="margin" readonly>
								</div>
							</div>

							<div class="mb-3 ms-3">
								<div class="form-check">
									<input class="form-check-input" type="checkbox" id="pricing_with_storehouse_management" name="with_storehouse_management"/>
									<label class="form-check-label" for="pricing_with_storehouse_management">With storehouse management</label>
								</div>
							</div>

							<div id="quantity_section" class="mb-3 ms-3">
								<div class="mb-3">
									<label for="quantity" class="form-label">Quantity</label>
									<input type="number" class="form-control" id="pricing_quantity" name="quantity">
								</div>

								<div class="form-check ms-3">
									<input class="form-check-input" type="checkbox" id="pricing_allow_checkout_when_out_of_stock" name="allow_checkout_when_out_of_stock">
									<label class="form-check-label" for="allowCheckout">
										Allow customer checkout when this product is out of stock
									</label>
								</div>
							</div>

							<div id="stock_status_section" class="mb-3 ms-3">
								<label class="form-label">Stock Status</label>
								<div class="d-flex flex-row">
									<div class="form-check me-3 ms-3">
										<input type="radio" id="pricing_in_stock" class="form-check-input" name="stock_status" value="in_stock" checked>
										<label for="pricing_in_stock" class="form-check-label">In stock</label>
									</div>
									<div class="form-check me-3 ms-3">
										<input type="radio" id="pricing_out_of_stock" class="form-check-input" name="stock_status" value="out_of_stock">
										<label for="pricing_out_of_stock" class="form-check-label">Out of stock</label>
									</div>
									<div class="form-check ms-3">
										<input type="radio" id="pricing_pre_order" class="form-check-input" name="stock_status" value="pre_order">
										<label for="pricing_pre_order" class="form-check-label">Pre-order</label>
									</div>
								</div>
							</div>

							<legend>
								<h5>Buy more Save more</h5>
							</legend>

							<div id="discount-group">
							</div>

							<div class="row">
								<div class="col-md-6 mb-3">
									<label for="storeSelect" class="form-label">Vendor</label>
									<select class="form-select" id="pricing_store_id" name="store_id">
										<option value="">Select a store</option>
										@foreach ($stores as $id => $name)
										<option value="{{ $id }}">{{ $name }}</option>
										@endforeach
									</select>
								</div>

								<div class="col-md-6 mb-3">
									<label for="variantRequiresShipping" class="form-label">Variant Requires Shipping</label>
									<select class="form-select" id="pricing_variant_requires_shipping" name="variant_requires_shipping">
										<option value="1">Yes</option>
										<option value="0">No</option>
									</select>
								</div>
							</div>

							<div class="row">
								<div class="col-md-6 mb-3">
									<label for="price" class="form-label">Minimum Order Quantity</label>
									<input type="number" class="form-control" id="pricing_minimum_order_quantity" name="minimum_order_quantity">
								</div>

								<div class="col-md-6 mb-3">
									<label for="refundPolicy" class="form-label">Refund Policy</label>
									<select id="pricing_refund" name="refund" class="form-control">
										<option value="non-refundable">Non-refundable</option>
										<option value="15 days">15 Days Refund</option>
										<option value="90 days">90 Days Refund</option>
									</select>
								</div>
							</div>

							<div class="row">
								<!-- Delivery Days -->
								<div class="col-md-6 mb-3">
									<label for="deliveryDays" class="form-label">Delivery Days</label>
									<input type="number" class="form-control" id="pricing_delivery_days" name="delivery_days" placeholder="Enter delivery days" min="1" step="1">
								</div>

								<!-- Box Quantity -->
								<div class="col-md-6 mb-3">
									<label for="boxQuantity" class="form-label">Box Quantity</label>
									<input type="number" class="form-control" id="pricing_box_quantity" name="box_quantity" placeholder="Enter box quantity" min="1" step="1">
								</div>
							</div>

							<div class="mb-3">
								<input type="hidden" id="pricing_initial_approval_status" name="initial_approval_status">
								<label for="pricing_approval_status" class="form-label">Approval Status</label>
								<select class="form-select" id="pricing_approval_status" name="approval_status">
									@foreach ($approvalStatuses as $value => $label)
									<option value="{{ $value }}">{{ $label }}</option>
									@endforeach
								</select>
							</div>

							<div class="mb-3">
								<label for="pricing_remarks" class="form-label">Remarks</label>
								<textarea class="form-select" id="pricing_remarks" name="remarks"></textarea>
							</div>

							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<!-- Pricing Modal -->

	<!-- Edit Content Modal -->
	<div class="modal fade" id="editContentModal" tabindex="-1" role="dialog" aria-labelledby="editContentModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="editContentModalLabel">Edit Product</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="{{ route('temp-products.approve') }}" method="POST">
						@csrf
						<div class="products-container">
							@foreach ($tempContentProducts as $tempContentProduct)
							<div class="product-card" id="product-row-{{ $tempContentProduct->id }}">
								<div class="product-header">
									<h6>Product ID: {{ $tempContentProduct->product_id }}</h6>
									<h4>{{ $tempContentProduct->name }}</h4>
								</div>

								<div class="product-description">
									<label for="description-{{ $tempContentProduct->id }}">Change Description:</label>
									<textarea id="description-{{ $tempContentProduct->id }}" class="editor" name="description[{{ $tempContentProduct->id }}]">
										{{ $tempContentProduct->description }}
									</textarea>
								</div>
								<div class="product-content">
									<label for="content-{{ $tempContentProduct->id }}">Change Content:</label>
									<textarea id="description-{{ $tempContentProduct->id }}" class="editor" name="content[{{ $tempContentProduct->id }}]">
										{{ $tempContentProduct->content }}
									</textarea>
								</div>
								<div class="approval-status-container">
									<label for="approval-status-{{ $tempContentProduct->id }}">Approval Status:</label>
									<select name="approval_status[{{ $tempContentProduct->id }}]" id="approval-status-{{ $tempContentProduct->id }}" class="form-control approval-status-dropdown">
										<option value="pending" {{ $tempContentProduct->approval_status == 'pending' ? 'selected' : '' }}>Pending</option>
										<option value="approved" {{ $tempContentProduct->approval_status == 'approved' ? 'selected' : '' }}>Approved</option>
										<option value="rejected" {{ $tempContentProduct->approval_status == 'rejected' ? 'selected' : '' }}>Rejected</option>
									</select>
								</div>
								<div class="edit-button-container">
									<button type="button" class="edit-icon" data-toggle="modal" data-target="#editContentModal"
									data-id="{{ $tempContentProduct->id }}"
									data-name="{{ $tempContentProduct->name }}"
									data-description="{{ $tempContentProduct->description }}"
									data-content="{{ $tempContentProduct->content }}"
									data-status="{{ $tempContentProduct->status }}"
									data-approval-status="{{ $tempContentProduct->approval_status }}"></button>
								</div>
							</div>
							@endforeach
						</div>
						<button type="submit" class="btn btn-success" id="save-changes-btn">Save Approval Changes</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<!-- Edit Content Modal -->

	<!-- jQuery -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

	<!-- Bootstrap JS -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
	<script>
		document.querySelectorAll('.editor').forEach((element) => {
			ClassicEditor
			.create(element)
			.catch((error) => {
				console.error(error);
			});
		});

		// tinymce.init({
		// 	selector: '.editor',
		// 	menubar: false,
		// 	toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image',
		// 	plugins: 'lists link image',
		// 	content_style: "body { font-family:Helvetica,Arial,sans-serif; font-size:14px }",
		// 	setup: function (editor) {
		// 		editor.on('change', function () {
		// 			editor.save(); // This ensures that the data is saved into the textarea
		// 		});
		// 	}
		// });
	</script>

	<style>

		.product-card {
			border: 1px solid #ddd;
			border-radius: 8px;
			padding: 15px;
			background-color: #f9f9f9;
			box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
			display: flex;
			flex-direction: column;
			justify-content: space-between;
		}
		.product-header {
			margin-bottom: 10px;
		}
		.product-status {
			margin: 10px 0;
			font-weight: bold;
		}
		.product-description ,   .product-content {
			margin-bottom: 10px;
		}
		.approval-status-container, .edit-button-container {
			display: flex;
			align-items: center;
			gap: 10px;
		}
		.editor {
			width: 100%;
			height: 150px; /* Set the height of the editor */
		}
	</style>

	<script>
		// Function to toggle the "To Date" field for each discount group
		function toggleToDateField(checkbox) {
			// Find the discount item container (group) that contains the checkbox
			const discountItem = checkbox.closest('.discount-item');

			// Get the "To Date" input field within this group
			const toDateInput = discountItem.querySelector('.to-date');

			// If "Never Expired" is checked, disable the "To Date" field
			if (checkbox.checked) {
				toDateInput.disabled = true;
			} else {
				toDateInput.disabled = false;
			}
		}

		function calculateDiscount(element) {
			const discountItem = element.closest('.discount-item');
			const productRequiredInput = discountItem.querySelector('.product-quantity');
			const discountPercentageInput = discountItem.querySelector('.discount-percentage');
			const priceAfterDiscountInput = discountItem.querySelector('.price-after-discount');
			const marginInput = discountItem.querySelector('.margin');

			const price = document.querySelector('input[name="sale_price"]').value || document.querySelector('input[name="price"]').value || 0;
			const costPerItem = document.querySelector('input[name="cost_per_item"]').value || 0;
			const productRequired = parseFloat(productRequiredInput.value) || 0;
			const discountPercentage = parseFloat(discountPercentageInput.value) || 0;

			// Ensure all inputs are valid
			if (price > 0 && productRequired > 0 && discountPercentage > 0) {
				// Calculate discount amount
				const discountAmount = price * (discountPercentage / 100);

				// Calculate final price after discount
				const priceAfterDiscount = price - discountAmount;

				// Set the result in the readonly input field
				priceAfterDiscountInput.value = priceAfterDiscount.toFixed(2);

				const marginValue = (priceAfterDiscountInput.value - costPerItem)*100/priceAfterDiscountInput.value;
				marginInput.value = marginValue.toFixed(2);
			} else {
				// Clear the price after discount field if inputs are invalid or missing
				priceAfterDiscountInput.value = '';
			}
		}

		function calculateMargin() {
			const price = document.querySelector('#pricing_sale_price').value || document.querySelector('#pricing_price').value || 0;
			const costPerItem = document.querySelector('#pricing_cost_per_item').value || 0;
			const marginInput = document.querySelector('#pricing_margin');

			if (price > 0 && costPerItem > 0) {
				const margin = ((price - costPerItem) / price) * 100;
				marginInput.value = `${margin.toFixed(2)}`;
			} else {
				marginInput.value = 0;
			}
		}

		const unitOfMeasurementDropdown = document.getElementById('pricing_unit_of_measurement_id');
		const unitLabels = {
			1: 'Pieces',
			2: 'Dozen',
			3: 'Box',
			4: 'Case'
		};

		// Function to update all quantity labels
		function updateAllQuantityLabels() {
			const selectedValue = unitOfMeasurementDropdown.value;
			const unitText = unitLabels[selectedValue] || 'Units';

			// Update all labels in the discount group
			document.querySelectorAll('.quantity-label').forEach((label, index) => {
				label.textContent = `Buying Quantity Tier ${index+1} (in ${unitText})`;
			});
		}

		$(document).on('click', '#edit_pricing_modal', function () {
			// Get the product data from the button's data-product attribute
			const productData = $(this).attr('data-product');
			const decodedData = $('<textarea/>').html(productData).text();

			// Parse the JSON string into a JavaScript object
			const product = JSON.parse(decodedData);

			// console.log('Parsed Product:', product.discount);

			// Populate the modal fields
			$('#pricing_temp_header_id').text(product.product_id);
			$('#pricing_temp_header_name').text(product.name);

			$('#pricing_temp_id').val(product.id);
			$('#pricing_sku').val(product.sku);
			$('#pricing_price').val(product.price);
			$('#pricing_sale_price').val(product.sale_price);
			$('#pricing_from_date').val(product.from_date);
			$('#pricing_to_date').val(product.to_date);
			$('#pricing_cost_per_item').val(product.cost_per_item);
			$('#pricing_margin').val(product.margin);
			$('#pricing_quantity').val(product.quantity);

			$('#pricing_store_id').val(product.store_id);
			$('#pricing_minimum_order_quantity').val(product.minimum_order_quantity);
			$('#pricing_box_quantity').val(product.box_quantity);
			$('#pricing_delivery_days').val(product.delivery_days);
			$('#pricing_unit_of_measurement_id').val(product.unit_of_measurement_id);
			$('#pricing_variant_requires_shipping').val(product.variant_requires_shipping);
			$('#pricing_refund').val(product.refund);
			$('#pricing_initial_approval_status').val(product.approval_status);
			$('#pricing_approval_status').val(product.approval_status);
			$('#pricing_remarks').val(product.remarks);

			// Set checkbox values
			$('#pricing_with_storehouse_management').prop('checked', product.with_storehouse_management);
			$('#pricing_allow_checkout_when_out_of_stock').prop('checked', product.allow_checkout_when_out_of_stock);
			$(`#pricing_${productData.stock_status}`).prop('checked', true);


			// Clear existing discount items
			const discountGroup = $('#discount-group');
			discountGroup.empty();

			// Populate discount items
			if (product.discount && product.discount.length) {
				product.discount.forEach((discount, index) => {
					const discountItem = `
						<div class="discount-item">
							<div class="row g-3 mb-3">
								<div class="col-md-6">
									<input type="hidden" name="discount[${index}][discount_id]" value="${discount.discount_id}">
									<label for="product_quantity_${index}" class="form-label quantity-label">Buying Quantity</label>
									<input type="number" class="form-control product-quantity"
										   name="discount[${index}][product_quantity]"
										   value="${discount.product_quantity || ''}"
										   onchange="calculateDiscount(this)">
								</div>

								<div class="col-md-6">
									<label for="discount_${index}" class="form-label">Discount (%)</label>
									<input type="number" class="form-control discount-percentage"
										   name="discount[${index}][discount]"
										   value="${discount.discount || ''}"
										   onchange="calculateDiscount(this)">
								</div>

								<div class="col-md-6">
									<label for="price_after_discount_${index}" class="form-label">Price after Discount</label>
									<input type="number" class="form-control price-after-discount"
										   name="discount[${index}][price_after_discount]"
										   value="${discount.price_after_discount || ''}" readonly>
								</div>

								<div class="col-md-6">
									<label for="margin_${index}" class="form-label">Margin (%)</label>
									<input type="number" class="form-control margin"
										   name="discount[${index}][margin]"
										   value="${discount.margin || ''}" readonly>
								</div>
							</div>

							<div class="row g-3 mb-3">
								<div class="col-md-4">
									<label for="fromDate_${index}" class="form-label">From Date</label>
									<input type="datetime-local" class="form-control"
										   name="discount[${index}][discount_from_date]"
										   value="${discount.discount_from_date || ''}">
								</div>

								<div class="col-md-4">
									<label for="toDate_${index}" class="form-label">To Date</label>
									<input type="datetime-local" class="form-control to-date"
											${discount.never_expired==1 ? 'disabled' : ''}
										   name="discount[${index}][discount_to_date]"
										   value="${discount.discount_to_date || ''}">
								</div>

								<div class="col-md-4 d-flex align-items-center">
									<div class="form-check">
										<input class="form-check-input me-2 never-expired-checkbox"
											   type="checkbox"
											   name="discount[${index}][never_expired]"
											   value="1"
											   ${discount.never_expired ? 'checked' : ''}
											   onchange="toggleToDateField(this)">
										<label class="form-check-label" for="never_expired_${index}">Never Expired</label>
									</div>
								</div>
							</div>

							<div class="row g-3 my-3">
								<div class="col-md-12">&nbsp;
								</div>
							</div>
						</div>
					`;
					discountGroup.append(discountItem);
				});

				// Add "Add" button if items are less than 3
				if (product.discount.length < 3) {
					discountGroup.append(`
						<div class="row g-3 mb-3">
							<div class="col-md-12 text-end">
								<button type="button" class="btn btn-success add-btn"><i class="fas fa-plus"></i> Add</button>
							</div>
						</div>
					`);
				}

				// Ensure the new label reflects the current UoM
				updateAllQuantityLabels();
			}

			// Show the discount period fields if the dates are available
			if (product.from_date || product.to_date) {
				$('#discountPeriodFields').removeClass('d-none');
			} else {
				$('#discountPeriodFields').addClass('d-none');
			}

			// Initially hide the storehouse fields if checkbox is unchecked
			if ($('#pricing_with_storehouse_management').is(':checked')) {
				$('#quantity_section').removeClass('d-none');
				$('#stock_status_section').addClass('d-none')
			} else {
				$('#quantity_section').addClass('d-none');
				$('#stock_status_section').removeClass('d-none');
			}

			$('#pricing_with_storehouse_management').val($('#pricing_with_storehouse_management').is(':checked') ? 1 : 0);
			$('#pricing_allow_checkout_when_out_of_stock').val($('#pricing_allow_checkout_when_out_of_stock').is(':checked') ? 1 : 0);

			// Toggle storehouse fields and checkbox value

			$('#pricing_with_storehouse_management').change(function () {
				if ($(this).is(':checked')) {
					$(this).val(1); // Set value to 1 when checked
					$('#quantity_section').removeClass('d-none');
					$('#stock_status_section').addClass('d-none')
				} else {
					$(this).val(0); // Set value to 0 when unchecked
					$('#quantity_section').addClass('d-none');
					$('#stock_status_section').removeClass('d-none');
				}
			});
			$('#pricing_allow_checkout_when_out_of_stock').change(function() {
				$(this).val(this.checked ? 1 : 0);
			});

			$('#chooseDiscountPeriod').click(function() {
				$('#discountPeriodFields').toggleClass('d-none');

				// Toggle text between "Choose Discount Period" and "Cancel"
				const linkText = $(this).text().trim();
				$(this).text(linkText === 'Choose Discount Period' ? 'Cancel' : 'Choose Discount Period');
			});


			// Get references to the select and textarea elements
			const $approvalStatus = $('#pricing_approval_status');
			const $remarks = $('#pricing_remarks');

			// Function to update the "required" attribute based on approval status
			function updateRemarksRequirement() {
				if ($approvalStatus.val() === 'rejected') { // Replace 'rejected' with the actual value for rejection
					$remarks.attr('required', 'required');
				} else {
					$remarks.removeAttr('required');
				}
			}

			// Initial check when the page loads
			updateRemarksRequirement();

			// Update requirement whenever the approval status changes
			$approvalStatus.on('change', updateRemarksRequirement);
		});

		const discountGroup = document.getElementById('discount-group');
		discountGroup.addEventListener('click', (event) => {
			if (event.target.classList.contains('add-btn')) {
				// Disable the "Add" button temporarily
				event.target.classList.add('disabled');
				event.target.disabled = true;

				/* Find the current count of discount items */
				const count = discountGroup.querySelectorAll('.discount-item').length;

				if (count < 3) {
					/* Create a new input field group with updated name attributes */
					const newField = document.createElement('div');
					newField.classList.add('discount-item');
					newField.innerHTML = `
						<div class="row g-3 mb-3">
							<div class="col-md-6">
								<label for="product_quantity" class="form-label quantity-label">Buying Quantity</label>
								<input type="number" class="form-control product-quantity" name="discount[${count}][product_quantity]" onchange="calculateDiscount(this)">
							</div>
							<div class="col-md-6">
								<label for="discount" class="form-label">Discount (%)</label>
								<input type="number" class="form-control discount-percentage" name="discount[${count}][discount]" onchange="calculateDiscount(this)">
							</div>
							<div class="col-md-6">
								<label for="price_after_discount" class="form-label">Price after Discount</label>
								<input type="number" class="form-control price-after-discount" name="discount[${count}][price_after_discount]" readonly>
							</div>
							<div class="col-md-6">
								<label for="margin" class="form-label">Margin (%)</label>
								<input type="number" class="form-control margin" name="discount[${count}][margin]" readonly>
							</div>
						</div>
						<div class="row g-3 mb-3">
							<div class="col-md-4">
								<label for="fromDate" class="form-label">From Date</label>
								<input type="datetime-local" class="form-control" name="discount[${count}][discount_from_date]">
							</div>
							<div class="col-md-4">
								<label for="toDate" class="form-label">To Date</label>
								<input type="datetime-local" class="form-control to-date" name="discount[${count}][discount_to_date]">
							</div>
							<div class="col-md-4 d-flex align-items-center">
								<div class="form-check">
									<input class="form-check-input me-2 never-expired-checkbox" type="checkbox" name="discount[${count}][never_expired]" value="1" onchange="toggleToDateField(this)">
									<label class="form-check-label" for="never_expired">Never Expired</label>
								</div>
							</div>
						</div>
						<div class="row g-3 mb-3">
							<div class="col-md-12 text-end">
								<button type="button" class="btn btn-danger remove-btn1"><i class="fas fa-minus"></i> Remove</button>
							</div>
						</div>
					`;
					discountGroup.appendChild(newField);

					// Ensure the new label reflects the current UoM
					updateAllQuantityLabels();
				}
			} else if (event.target.classList.contains('remove-btn1')) {
				/* Remove input fields */
				const discountItem = event.target.closest('.discount-item');
				if (discountItem) {
					discountItem.remove();
				}

				// Re-enable the Add button after a remove
				const addButton = discountGroup.querySelector('.add-btn');
				if (addButton) {
					addButton.classList.remove('disabled');
					addButton.disabled = false;
				}
			}
		});

		// Trigger label updates when the UoM dropdown changes
		unitOfMeasurementDropdown.addEventListener('change', updateAllQuantityLabels);
	</script>
	<script>
		$(document).ready(function () {
		// Edit Product button click
			$(document).on('click', '.edit-icon', function () {
				var currentProductId = $(this).data('id');
				var productName = $(this).data('name');
				var productDescription = $(this).data('description');
				var productContent = $(this).data('content');
				var approvalStatus = $(this).data('approval-status');

			// Populate modal fields
				$('#edit-product-id').val(currentProductId);
				$('#product-name').val(productName);
				$('#product-description').val(productDescription);
				$('#product-content').val(productContent);
				$('#approval-status').val(approvalStatus);
			});
		});
	</script>

</body>

@endsection