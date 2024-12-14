@extends($layout ?? BaseHelper::getAdminMasterLayoutTemplate())

@section('content')

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Edit Temp Products</title>

	<!-- Bootstrap CSS -->
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet">
	<link rel="icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/96x96.png" sizes="96x96">
	<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/120x120.png" sizes="120x120">
	<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/152x152.png" sizes="152x152">
	<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/167x167.png" sizes="167x167">
	<link rel="apple-touch-icon" type="image/png" href="https://ckeditor.com/assets/images/favicons/180x180.png" sizes="180x180">
	<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/44.0.0/ckeditor5.css" crossorigin>
	<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5-premium-features/44.0.0/ckeditor5-premium-features.css" crossorigin>
</head>
<body>
	<div class="row">
		<div class="col-md-3 mb-3">
			<label class="form-label bg-info text-white text-center py-3 h6">Content In Progress<br/><span class="h2">{{ $tempContentProducts->where('approval_status', 'in-process')->count() }}</span></label>
		</div>
		<div class="col-md-3 mb-3">
			<label class="form-label bg-warning text-white text-center py-3 h6">Submitted for Approval<br/><span class="h2">{{ $tempContentProducts->where('approval_status', 'pending')->count() }}</span></label>
		</div>
		<div class="col-md-3 mb-3">
			<label class="form-label bg-success text-white text-center py-3 h6">Ready to Publish<br/><span class="h2">{{ $tempContentProducts->where('approval_status', 'approved')->count() }}</span></label>
		</div>
		<div class="col-md-3 mb-3">
			<label class="form-label bg-danger text-white text-center py-3 h6">Rejected for Corrections<br/><span class="h2">{{ $tempContentProducts->sum('rejection_count') }}</span></label>
		</div>
	</div>
	<div class="table-responsive">
		<table class="table table-striped">
			<thead>
				<tr>
					<th>Product ID</th>
					<th>Product Name</th>
					<th>Changed Description</th>
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
						<td class="product-description">{{ $approvalStatuses[$tempContentProduct->approval_status] ?? '' }}</td>
						<td>
							@if($tempContentProduct->approval_status == 'in-process' || $tempContentProduct->approval_status == 'rejected')
								<button type="button" id="edit_content_modal" data-toggle="modal" data-target="#editContentModal"
								data-id="{{ $tempContentProduct->id }}"
								data-name="{{ $tempContentProduct->name }}"
								data-description="{{ $tempContentProduct->description }}"
								data-content="{{ $tempContentProduct->content }}"
								data-remarks="{{ $tempContentProduct->remarks }}"
								data-product_id="{{ $tempContentProduct->product_id }}"><i class="fas fa-pencil-alt"></i></button>
							@endif
						</td>
						{{-- <td>{{ $tempContentProduct->product_id }}</td>
						<td class="product-name">{{ $tempContentProduct->name }}</td>
						<td class="product-description">{{ $tempContentProduct->sku }}</td>
						<td class="product-description">{{ $approvalStatuses[$tempContentProduct->approval_status] ?? '' }}</td>
						<td>
							@if($tempContentProduct->approval_status == 'in-process' || $tempContentProduct->approval_status == 'rejected')
								<button type="button" id="edit_content_modal" data-toggle="modal" data-target="#editContentModal" data-product="{{ htmlspecialchars(json_encode($tempContentProduct->toArray(), JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') }}">
									<i class="fas fa-pencil-alt"></i>
								</button>
							@endif
						</td> --}}
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>

	<!-- Content Modal -->
	<div class="modal fade" id="editContentModal" tabindex="-1" role="dialog" aria-labelledby="editContentModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg content-model" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="editContentModalLabel">Edit Product</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="{{ route('temp-products.content_update') }}" method="POST">
						@csrf
						<div class="product-card">
							<div class="product-header">
								<h6>Product ID: <span id="content_temp_header_id"></span></h6>
								<h4 id="content_temp_header_name"></h4>
								<input type="hidden" id="content_temp_id" name="id">
							</div>

							<div class="row">
								<div class="mb-3 col-md-12">
									<div class="main-container">
										<div class="presence" id="editor-presence"></div>
										<div class="editor-container editor-container_document-editor editor-container_include-annotations" id="editor-container">
											<div class="editor-container__toolbar" id="editor-toolbar"></div>
											<div class="editor-container__editor-wrapper">
												<div class="editor-container__editor"><div id="editor">

													<div id="product-description"></div>

												</div></div>
												<div class="editor-container__sidebar"><div id="editor-annotations"></div></div>
											</div>
										</div>
										<div class="revision-history" id="editor-revision-history">
											<div class="revision-history__wrapper">
												<div class="revision-history__editor" id="editor-revision-history-editor"></div>
												<div class="revision-history__sidebar" id="editor-revision-history-sidebar"></div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="row">
								<div class="mb-3 col-md-12">
									<label for="content_content" class="form-label">Change Content:</label>
									<textarea class="editor" id="content_content" name="content"></textarea>
								</div>
							</div>

							{{-- <div class="product-content">
								<label for="content-{{ $tempContentProduct->id }}">Change Content:</label>
								<textarea id="description-{{ $tempContentProduct->id }}" class="editor" name="content[{{ $tempContentProduct->id }}]">
									{{ $tempContentProduct->content }}
								</textarea>
							</div> --}}

							<div class="row g-3 mb-3 ms-1">
								<div class="col-md-4 d-flex align-items-center">
									<div class="form-check">
										<input class="form-check-input me-2" type="checkbox" id="pricing_in_process" name="in_process" value="1">
										<label class="form-check-label" for="in_process">Is Draft</label>
									</div>
								</div>
							</div>

							<div class="mb-3">
								<label for="content_remarks" class="form-label">Remarks</label>
								<textarea class="form-select" id="content_remarks" name="remarks" readonly></textarea>
							</div>
							<button type="submit" class="btn btn-primary">Submit</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
	<!-- Content Modal -->

	<!-- jQuery -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

	<!-- Bootstrap JS -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
	<script src="https://cdn.ckeditor.com/ckeditor5/44.0.0/ckeditor5.umd.js" crossorigin></script>
	<script src="https://cdn.ckeditor.com/ckeditor5-premium-features/44.0.0/ckeditor5-premium-features.umd.js" crossorigin></script>

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

	<style>
		@import url('https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,400;0,700;1,400;1,700&display=swap');

		@media print {
			body {
				margin: 0 !important;
			}
		}

		.modal-dialog.modal-lg.content-model {
		    max-width: 85% !important;
		}
		.main-container {
			--ckeditor5-preview-sidebar-width: 270px;
			--ckeditor5-preview-height: 700px;
			font-family: 'Lato';
			width: fit-content;
			margin-left: auto;
			margin-right: auto;
		}

		.main-container .presence {
			margin-bottom: 8px;
		}

		.ck-content {
			font-family: 'Lato';
			line-height: 1.6;
			word-break: break-word;
		}

		.editor-container__editor-wrapper {
			display: flex;
			width: fit-content;
		}

		.editor-container_document-editor {
			border: 1px solid var(--ck-color-base-border);
		}

		.editor-container_document-editor .editor-container__toolbar {
			display: flex;
			position: relative;
			box-shadow: 0 2px 3px hsla(0, 0%, 0%, 0.078);
		}

		.editor-container_document-editor .editor-container__toolbar > .ck.ck-toolbar {
			flex-grow: 1;
			width: 0;
			border-bottom-right-radius: 0;
			border-bottom-left-radius: 0;
			border-top: 0;
			border-left: 0;
			border-right: 0;
		}

		.editor-container_document-editor .editor-container__editor-wrapper {
			max-height: var(--ckeditor5-preview-height);
			min-height: var(--ckeditor5-preview-height);
			overflow-y: scroll;
			background: var(--ck-color-base-foreground);
		}

		.editor-container_document-editor .editor-container__editor {
			margin-top: 28px;
			margin-bottom: 28px;
			height: 100%;
		}

		.editor-container_document-editor .editor-container__editor .ck.ck-editor__editable {
			box-sizing: border-box;
			min-width: calc(210mm + 2px);
			max-width: calc(210mm + 2px);
			min-height: 297mm;
			height: fit-content;
			padding: 20mm 12mm;
			border: 1px hsl(0, 0%, 82.7%) solid;
			background: hsl(0, 0%, 100%);
			box-shadow: 0 2px 3px hsla(0, 0%, 0%, 0.078);
			flex: 1 1 auto;
			margin-left: 72px;
			margin-right: 72px;
		}

		.editor-container_include-annotations .editor-container__editor .ck.ck-editor__editable {
			margin-right: 0;
		}

		.editor-container__sidebar {
			min-width: var(--ckeditor5-preview-sidebar-width);
			max-width: var(--ckeditor5-preview-sidebar-width);
			margin-top: 28px;
			margin-left: 10px;
			margin-right: 10px;
		}

		.revision-history {
			display: none;
		}

		.revision-history__wrapper {
			display: flex;
		}

		.revision-history__wrapper .ck.ck-editor {
			margin: 0;
			width: 795px;
		}

		.revision-history__wrapper .revision-history__sidebar {
			border: 1px solid var(--ck-color-base-border);
			border-left: 0;
			width: var(--ckeditor5-preview-sidebar-width);
			min-height: 100%;
		}

		.revision-history__wrapper .revision-history__sidebar .ck-revision-history-sidebar {
			height: 100%;
		}
	</style>
	<script>
		$(document).on('click', '#edit_content_modal', function () {
			const {
				DecoupledEditor,
				Plugin,
				ButtonView,
				Alignment,
				AutoLink,
				Autosave,
				BalloonToolbar,
				Bold,
				Bookmark,
				CloudServices,
				Code,
				Essentials,
				FontBackgroundColor,
				FontColor,
				FontFamily,
				FontSize,
				Heading,
				HorizontalLine,
				Indent,
				IndentBlock,
				Italic,
				Link,
				Mention,
				Paragraph,
				RemoveFormat,
				SpecialCharacters,
				Strikethrough,
				Subscript,
				Superscript,
				Underline
			} = window.CKEDITOR;
			const {
				Comments,
				PresenceList,
				RealTimeCollaborativeComments,
				RealTimeCollaborativeEditing,
				RealTimeCollaborativeRevisionHistory,
				RealTimeCollaborativeTrackChanges,
				RevisionHistory,
				TrackChanges,
				TrackChangesData
			} = window.CKEDITOR_PREMIUM_FEATURES;

			const LICENSE_KEY =
				'eyJhbGciOiJFUzI1NiJ9.eyJleHAiOjE3MzQ0Nzk5OTksImp0aSI6IjczYzUxOWZiLWYyZjctNGVjMC1iMTM5LWNkOTU0MzU4Yzc3NCIsInVzYWdlRW5kcG9pbnQiOiJodHRwczovL3Byb3h5LWV2ZW50LmNrZWRpdG9yLmNvbSIsImRpc3RyaWJ1dGlvbkNoYW5uZWwiOlsiY2xvdWQiLCJkcnVwYWwiLCJzaCJdLCJ3aGl0ZUxhYmVsIjp0cnVlLCJsaWNlbnNlVHlwZSI6InRyaWFsIiwiZmVhdHVyZXMiOlsiKiJdLCJ2YyI6ImQzMmJmNTU4In0.8EvUlDwSuoJdVQ2uh4y5BmFRB9Port946VGHEhSx8FczXiFGwOWf8_pxw7NSexKsGMuK_J4U8OSukgzU3CJvAw';

			const CLOUD_SERVICES_TOKEN_URL = 'https://124068.cke-cs.com/token/dev/ff1755ae0a1f80bc0c7eff88a367666925e5d9dc5b87e982947f42b43863?limit=10';
			const CLOUD_SERVICES_WEBSOCKET_URL = 'wss://124068.cke-cs.com/ws';

			var id = $(this).data('id');
			var name = $(this).data('name');
			var description = $(this).data('description');
			var content = $(this).data('content');
			var productID = $(this).data('product_id');
			var remarks = $(this).data('remarks');

			// Populate the modal fields
			$('#content_temp_header_id').text(productID);
			$('#content_temp_header_name').text(name);

			$('#content_temp_id').val(id);
			$('#content_content').val(content);
			$('#content_remarks').val(remarks);

			/**
			 * The `AnnotationsSidebarToggler` plugin adds an icon to the right side of the editor.
			 *
			 * It allows to toggle the right annotations bar visibility.
			 */
			class AnnotationsSidebarToggler extends Plugin {
				static get requires() {
					return ['AnnotationsUIs'];
				}

				static get pluginName() {
					return 'AnnotationsSidebarToggler';
				}

				init() {
					this.toggleButton = new ButtonView(this.editor.locale);

					const NON_COLLAPSE_ANNOTATION_ICON =
						'<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" transform="matrix(-1,0,0,1,0,0)"><path d="M11.463 5.187a.888.888 0 1 1 1.254 1.255L9.16 10l3.557 3.557a.888.888 0 1 1-1.254 1.255L7.26 10.61a.888.888 0 0 1 .16-1.382l4.043-4.042z"></path></svg>';
					const COLLAPSE_ANNOTATION_ICON =
						'<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" transform="matrix(1,0,0,1,0,0)"><path d="M11.463 5.187a.888.888 0 1 1 1.254 1.255L9.16 10l3.557 3.557a.888.888 0 1 1-1.254 1.255L7.26 10.61a.888.888 0 0 1 .16-1.382l4.043-4.042z"/></svg>';

					const annotationsUIsPlugin = this.editor.plugins.get('AnnotationsUIs');
					const annotationsContainer = this.editor.config.get('sidebar.container');
					const sidebarContainer = annotationsContainer.parentElement;

					this.toggleButton.set({
						label: 'Toggle annotations sidebar',
						tooltip: 'Hide annotations sidebar',
						tooltipPosition: 'se',
						icon: COLLAPSE_ANNOTATION_ICON
					});

					this.toggleButton.on('execute', () => {
						// Toggle a CSS class on the annotations sidebar container to manage the visibility of the sidebar.
						annotationsContainer.classList.toggle('ck-hidden');

						// Change the look of the button to reflect the state of the annotations container.
						if (annotationsContainer.classList.contains('ck-hidden')) {
							this.toggleButton.icon = NON_COLLAPSE_ANNOTATION_ICON;
							this.toggleButton.tooltip = 'Show annotations sidebar';
							annotationsUIsPlugin.switchTo('inline');
						} else {
							this.toggleButton.icon = COLLAPSE_ANNOTATION_ICON;
							this.toggleButton.tooltip = 'Hide annotations sidebar';
							annotationsUIsPlugin.switchTo('wideSidebar');
						}

						// Keep the focus in the editor whenever the button is clicked.
						this.editor.editing.view.focus();
					});

					this.toggleButton.render();

					sidebarContainer.insertBefore(this.toggleButton.element, annotationsContainer);
				}

				destroy() {
					this.toggleButton.element.remove();

					return super.destroy();
				}
			}

			const documentID = productID.toString();
			const productDescription = $(this).data('description');
			const editorConfig = {
				toolbar: {
					items: [
						'revisionHistory',
						'trackChanges',
						'comment',
						'commentsArchive',
						'|',
						'heading',
						'|',
						'fontSize',
						'fontFamily',
						'fontColor',
						'fontBackgroundColor',
						'|',
						'bold',
						'italic',
						'underline',
						'strikethrough',
						'subscript',
						'superscript',
						'code',
						'removeFormat',
						'|',
						'specialCharacters',
						'horizontalLine',
						'link',
						'bookmark',
						'|',
						'alignment',
						'|',
						'outdent',
						'indent'
					],
					shouldNotGroupWhenFull: false
				},
				plugins: [
					Alignment,
					AutoLink,
					Autosave,
					BalloonToolbar,
					Bold,
					Bookmark,
					CloudServices,
					Code,
					Comments,
					Essentials,
					FontBackgroundColor,
					FontColor,
					FontFamily,
					FontSize,
					Heading,
					HorizontalLine,
					Indent,
					IndentBlock,
					Italic,
					Link,
					Mention,
					Paragraph,
					PresenceList,
					RealTimeCollaborativeComments,
					RealTimeCollaborativeEditing,
					RealTimeCollaborativeRevisionHistory,
					RealTimeCollaborativeTrackChanges,
					RemoveFormat,
					RevisionHistory,
					SpecialCharacters,
					Strikethrough,
					Subscript,
					Superscript,
					TrackChanges,
					TrackChangesData,
					Underline
				],
				extraPlugins: [AnnotationsSidebarToggler],
				balloonToolbar: ['comment', '|', 'bold', 'italic', '|', 'link'],
				cloudServices: {
					tokenUrl: CLOUD_SERVICES_TOKEN_URL,
					webSocketUrl: CLOUD_SERVICES_WEBSOCKET_URL
				},
				collaboration: {
					channelId: documentID
				},
				comments: {
					editorConfig: {
						extraPlugins: [Bold, Italic, Mention],
						mention: {
							feeds: [
								{
									marker: '@',
									feed: [
										/* See: https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html#comments-with-mentions */
									]
								}
							]
						}
					}
				},
				fontFamily: {
					supportAllValues: true
				},
				fontSize: {
					options: [10, 12, 14, 'default', 18, 20, 22],
					supportAllValues: true
				},
				heading: {
					options: [
						{
							model: 'paragraph',
							title: 'Paragraph',
							class: 'ck-heading_paragraph'
						},
						{
							model: 'heading1',
							view: 'h1',
							title: 'Heading 1',
							class: 'ck-heading_heading1'
						},
						{
							model: 'heading2',
							view: 'h2',
							title: 'Heading 2',
							class: 'ck-heading_heading2'
						},
						{
							model: 'heading3',
							view: 'h3',
							title: 'Heading 3',
							class: 'ck-heading_heading3'
						},
						{
							model: 'heading4',
							view: 'h4',
							title: 'Heading 4',
							class: 'ck-heading_heading4'
						},
						{
							model: 'heading5',
							view: 'h5',
							title: 'Heading 5',
							class: 'ck-heading_heading5'
						},
						{
							model: 'heading6',
							view: 'h6',
							title: 'Heading 6',
							class: 'ck-heading_heading6'
						}
					]
				},
				initialData:productDescription,
							licenseKey: LICENSE_KEY,
				link: {
					addTargetToExternalLinks: true,
					defaultProtocol: 'https://',
					decorators: {
						toggleDownloadable: {
							mode: 'manual',
							label: 'Downloadable',
							attributes: {
								download: 'file'
							}
						}
					}
				},
				mention: {
					feeds: [
						{
							marker: '@',
							feed: [
								/* See: https://ckeditor.com/docs/ckeditor5/latest/features/mentions.html */
							]
						}
					]
				},
				placeholder: 'Type or paste your content here!',
				presenceList: {
					container: document.querySelector('#editor-presence')
				},
				revisionHistory: {
					editorContainer: document.querySelector('#editor-container'),
					viewerContainer: document.querySelector('#editor-revision-history'),
					viewerEditorElement: document.querySelector('#editor-revision-history-editor'),
					viewerSidebarContainer: document.querySelector('#editor-revision-history-sidebar'),
					resumeUnsavedRevision: true
				},
				sidebar: {
					container: document.querySelector('#editor-annotations')
				}
			};

			configUpdateAlert(editorConfig);
			DecoupledEditor.create(document.querySelector('#editor'), editorConfig).then(editor => {
				document.querySelector('#editor-toolbar').appendChild(editor.ui.view.toolbar.element);

				return editor;
			});

			/**
			 * This function exists to remind you to update the config needed for premium features.
			 * The function can be safely removed. Make sure to also remove call to this function when doing so.
			 */
			function configUpdateAlert(config) {
				if (configUpdateAlert.configUpdateAlertShown) {
					return;
				}

				const isModifiedByUser = (currentValue, forbiddenValue) => {
					if (currentValue === forbiddenValue) {
						return false;
					}

					if (currentValue === undefined) {
						return false;
					}

					return true;
				};

				const valuesToUpdate = [];

				configUpdateAlert.configUpdateAlertShown = true;

				if (!isModifiedByUser(config.licenseKey, '<YOUR_LICENSE_KEY>')) {
					valuesToUpdate.push('LICENSE_KEY');
				}

				if (!isModifiedByUser(config.cloudServices?.tokenUrl, '<YOUR_CLOUD_SERVICES_TOKEN_URL>')) {
					valuesToUpdate.push('CLOUD_SERVICES_TOKEN_URL');
				}

				if (!isModifiedByUser(config.cloudServices?.webSocketUrl, '<YOUR_CLOUD_SERVICES_WEBSOCKET_URL>')) {
					valuesToUpdate.push('CLOUD_SERVICES_WEBSOCKET_URL');
				}

				if (valuesToUpdate.length) {
					window.alert(
						[
							'Please update the following values in your editor config',
							'to receive full access to Premium Features:',
							'',
							...valuesToUpdate.map(value => ` - ${value}`)
						].join('\n')
					);
				}
			}
		});
	</script>
</body>

@endsection