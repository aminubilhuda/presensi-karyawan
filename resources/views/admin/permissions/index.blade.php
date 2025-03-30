@extends('layouts.app')

@section('title', 'Manajemen Izin Akses')

@section('styles')
<!-- Select2 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
<style>
    .permission-group {
        margin-bottom: 30px;
    }
    
    .permission-group-title {
        font-weight: 600;
        text-transform: uppercase;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #eee;
    }
    
    .permission-item {
        margin-bottom: 8px;
    }
    
    .select-all-btn {
        margin-bottom: 15px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pilih Peran</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Pilih peran yang ingin dikelola izin aksesnya.</p>
                    
                    <div class="form-group">
                        <select id="role-selector" class="form-control select2">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i> Perubahan izin akses akan mempengaruhi semua pengguna dengan peran tersebut.
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Izin Akses: <span id="selected-role-name"></span></h5>
                    
                    <div>
                        <button type="button" class="btn btn-sm btn-primary" id="select-all-btn">
                            <i class="fas fa-check-square mr-1"></i> Pilih Semua
                        </button>
                        <button type="button" class="btn btn-sm btn-secondary" id="deselect-all-btn">
                            <i class="fas fa-square mr-1"></i> Batal Semua
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <form id="permissions-form" action="" method="POST">
                        @csrf
                        
                        <div id="permissions-container">
                            @foreach($permissions as $module => $modulePermissions)
                                <div class="permission-group">
                                    <h6 class="permission-group-title">{{ ucfirst($module) }}</h6>
                                    
                                    <div class="row">
                                        @foreach($modulePermissions as $permission)
                                            <div class="col-md-6">
                                                <div class="permission-item">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input permission-checkbox" 
                                                               id="permission-{{ $permission->id }}" name="permissions[]" 
                                                               value="{{ $permission->id }}">
                                                        <label class="custom-control-label" for="permission-{{ $permission->id }}" 
                                                               data-toggle="tooltip" title="{{ $permission->description }}">
                                                            {{ $permission->display_name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="text-right">
                            <button type="submit" class="btn btn-success" id="save-permissions-btn">
                                <i class="fas fa-save mr-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(function () {
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        
        // Function to load permissions for selected role
        function loadRolePermissions(roleId) {
            // Clear all checkboxes
            $('.permission-checkbox').prop('checked', false);
            
            // Set the role name in the title
            const roleName = $('#role-selector option:selected').text();
            $('#selected-role-name').text(roleName);
            
            // Update form action URL
            $('#permissions-form').attr('action', "{{ url('admin/permissions') }}/" + roleId);
            
            console.log('Loading permissions for role: ' + roleId + ' (' + roleName + ')');
            console.log('Form action set to: ' + $('#permissions-form').attr('action'));
            
            // Load permissions for the selected role
            $.ajax({
                url: "{{ url('admin/permissions') }}/" + roleId + "/get",
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    console.log('Permissions loaded successfully:', data);
                    // Check the checkboxes for the role's permissions
                    if (data.permissions && data.permissions.length > 0) {
                        data.permissions.forEach(function(permissionId) {
                            $('#permission-' + permissionId).prop('checked', true);
                        });
                        console.log('Checked ' + data.permissions.length + ' permissions');
                    } else {
                        console.log('No permissions found for this role');
                    }
                },
                error: function(error) {
                    console.error('Error loading permissions:', error);
                    alert('Terjadi kesalahan saat memuat izin akses.');
                }
            });
        }
        
        // Load permissions when role changes
        $('#role-selector').on('change', function() {
            const roleId = $(this).val();
            loadRolePermissions(roleId);
        });
        
        // Select all permissions
        $('#select-all-btn').on('click', function() {
            $('.permission-checkbox').prop('checked', true);
            console.log('All permissions selected');
        });
        
        // Deselect all permissions
        $('#deselect-all-btn').on('click', function() {
            $('.permission-checkbox').prop('checked', false);
            console.log('All permissions deselected');
        });
        
        // Initial load for the first role
        const initialRoleId = $('#role-selector').val();
        if (initialRoleId) {
            loadRolePermissions(initialRoleId);
        }
    });
</script>
@endsection 