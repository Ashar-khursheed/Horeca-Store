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
								<button type="button" class="edit-icon" data-toggle="modal" data-target="#editProductModal"
								data-id="{{ $tempContentProduct->id }}"
								data-name="{{ $tempContentProduct->name }}"
								data-description="{{ $tempContentProduct->description }}"
								data-content="{{ $tempContentProduct->content }}"
								data-status="{{ $tempContentProduct->status }}"
								data-approval-status="{{ $tempContentProduct->approval_status }}">
								<i class="fas fa-pencil-alt"></i> <!-- Pencil icon -->
							</button>
						</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>

		<button type="submit" class="btn btn-success" id="save-changes-btn">Save Approval Changes</button>
	</form>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
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
								<button type="button" class="edit-icon" data-toggle="modal" data-target="#editProductModal"
								data-id="{{ $tempContentProduct->id }}"
								data-name="{{ $tempContentProduct->name }}"
								data-description="{{ $tempContentProduct->description }}"
								data-content="{{ $tempContentProduct->content }}"
								data-status="{{ $tempContentProduct->status }}"
								data-approval-status="{{ $tempContentProduct->approval_status }}">

							</button>
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
</script>

<script>
	tinymce.init({
		selector: '.editor',
		menubar: false,
		toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image',
		plugins: 'lists link image',
		content_style: "body { font-family:Helvetica,Arial,sans-serif; font-size:14px }",
		setup: function (editor) {
			editor.on('change', function () {
				editor.save(); // This ensures that the data is saved into the textarea
			});
		}
	});
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