@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container mt-4">
	<h2>Edit Category</h2>

	<!-- Form -->
	<form action="{{ route('categoryFilter.update', $category->id) }}" method="POST">
		@csrf
		@method('PUT')

		<div class="row">
			<div class="mb-3 col-md-12">
				<label for="category" class="form-label">Category</label>
				<input type="text" class="form-control" id="category" value="{{ $category->name }}" readonly>
			</div>
		</div>

		<div class="row">
			<div class="mb-3 col-md-12">
				<label for="product_types" class="form-label">Product Types</label>
				<select id="product_types" name="product_types[]" class="form-control select2" multiple>
					@foreach ($productTypes as $type)
						<option value="{{ $type->id }}" {{ in_array($type->id, $category->productTypes->pluck('id')->toArray()) ? 'selected' : '' }}>
							{{ $type->name }}
						</option>
					@endforeach
				</select>
			</div>
		</div>

		<!-- Specifications -->
		<div class="form-group">
			<label for="specifications">Specifications</label>
			<div id="specification-container">
				@foreach ($category->specifications as $index => $specification)
					<div class="d-flex mb-2">
						<input type="text" name="specifications[]" value="{{ $specification->specification_name }}" class="form-control" placeholder="Specification {{ $index + 1 }}" />
						@if ($index >= 3)
							<button type="button" class="btn btn-danger ml-2 remove-specification">Remove</button>
						@endif
					</div>
				@endforeach

				<!-- Add minimum 3 empty text boxes if specifications are less -->
				@for ($i = $category->specifications->count(); $i < 3; $i++)
					<div class="d-flex mb-2">
						<input type="text" name="specifications[]" class="form-control" placeholder="Specification {{ $i + 1 }}" />
					</div>
				@endfor
			</div>
		</div>

		<div class="row g-3 mb-3">
			<div class="col-md-12 text-end">
				<button type="button" class="btn btn-success" id="add-specification"><i class="fas fa-plus"></i> Add</button>
			</div>
		</div>

		<!-- Submit Button -->
		<button type="submit" class="btn btn-success">Save Changes</button>
	</form>
</div>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		// Initialize Select2
		$('#product_types').select2({
			placeholder: "Select Product Types",
			allowClear: true
		});

		// Dynamic Specifications Logic
		const container = document.getElementById('specification-container');
		const addBtn = document.getElementById('add-specification');

		addBtn.addEventListener('click', () => {
			const specs = container.querySelectorAll('input[name="specifications[]"]');
			if (specs.length >= 60) return;

			const div = document.createElement('div');
			div.classList.add('d-flex', 'mb-2');
			div.innerHTML = `
				<input type="text" name="specifications[]" class="form-control" placeholder="Specification ${specs.length + 1}" />
				<button type="button" class="btn btn-danger ml-2 remove-specification">Remove</button>
			`;
			container.appendChild(div);

			div.querySelector('.remove-specification').addEventListener('click', () => {
				div.remove();
			});
		});

		container.addEventListener('click', function (event) {
			if (event.target.classList.contains('remove-specification')) {
				event.target.closest('.d-flex').remove();
			}
		});
	});
</script>
@endsection
