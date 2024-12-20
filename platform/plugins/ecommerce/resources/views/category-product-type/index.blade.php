@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
<div class="container mt-4">
	<!-- Table -->
	<table class="table table-bordered table-hover">
		<thead>
			<tr>
				<th class="fw-bold fs-6">ID</th>
				<th class="fw-bold fs-5">Category Name</th>
				<th class="fw-bold fs-5">Product Types</th>
				<th class="fw-bold fs-5">Specifications</th>
				<th class="fw-bold fs-5">Actions</th>
			</tr>
		</thead>
		<tbody>
			@foreach($categories as $category)
			<tr>
				<td>{{ $category['id'] }}</td>
				<td>{{ $category['name'] }}</td>
				<td>{{ $category['product_types'] }}</td>
				<td>{{ $category['specifications'] }}</td>
				<td>
					<a href="{{ route('categoryFilter.edit', $category['id']) }}" class="btn btn-sm btn-warning" title="Edit">
						<i class="fas fa-edit"></i>
					</a>
				</td>
			</tr>
			@endforeach
		</tbody>
	</table>

	<!-- Pagination Links -->
	<div class="d-flex justify-content-center">
		{{ $categories->links('pagination::bootstrap-4') }}
	</div>
</div>
@endsection
