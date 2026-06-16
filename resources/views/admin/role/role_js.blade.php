function deleteAdminProfile(id) {
                var result = confirm("{{ __('Are you sure want to delete?') }}");
                if (result) {
                    window.location.href = "{{ url('') }}/admin/admin-delete-"+id;
                }
            }
            function deleteRolePermission(id) {
                var result = confirm("{{ __('Are you sure want to delete?') }}");
                if (result) {
                    window.location.href = "{{ url('') }}/admin/role-delete-"+id;
                }
            }
            function resetAllRouts() {
                var result = confirm("{{ __('Are you sure want to reset routes?') }}");
                if (result) {
                    $.get("{{ url('') }}/admin/permission-route-reset",function(data){
                        window.location.reload(true);
                    });
                }
            }
            function deletePermissionRoute(id) {
                var result = confirm("{{ __('Are you sure want to delete route ?') }}");
                if (result) {
                    window.location.href = "{{ url('') }}/admin/permission-route-delete-"+id;
                }
            }
            function editPermissionRoute(id) {
                var result = confirm("{{ __('Are you sure want to edit route ?') }}");
                if (result) {
                    $.get("{{ url('') }}/admin/permission-route-edit-"+id,function(data){

                        if(data.success){
                            $('#exampleModalRoute input[name="id"]').remove();
                            $('#exampleModalRoute form').append('<input type="hidden" class="form-control" name="id"  value="'+ data.data.route_id +'">');
                            $('#exampleModalRoute input[name="action"]').val(data.data.action);
                            $('#exampleModalRoute input[name="for"]').val(data.data.for);
                            $('#exampleModalRoute input[name="group"]').val(data.data.group);
                            $('#exampleModalRoute input[name="route"]').val(data.data.route);
                            $('#exampleModalRoute').modal('show');
                            return 1;
                        }
                        $('#exampleModalRoute input[name="action"]').val('');
                        $('#exampleModalRoute input[name="for"]').val('');
                        $('#exampleModalRoute input[name="group"]').val('');
                        $('#exampleModalRoute input[name="route"]').val('');
                        return 0;
                    });
                }
            }

        (function($) {
            "use strict";

            $('#exampleModalRoute').on('hidden.bs.modal', function (e) {
                $('#exampleModalRoute input[name="id"]').remove();
                $('#exampleModalRoute input[name="action"]').val('');
                $('#exampleModalRoute input[name="for"]').val('');
                $('#exampleModalRoute input[name="group"]').val('');
                $('#exampleModalRoute input[name="route"]').val('');
            })

            @if(isset($errors->all()[0]))

                $('.tab-pane').removeClass('active show');
                $('.nav-link').removeClass('active show');
                $('.add_user').addClass('active show');
                $('#profile-tab').addClass('active show');

            @endif

            function getTable(type) {

                var table = $('#table').DataTable({
                    processing: true,
                    serverSide: true,
                    pageLength: 10,
                    retrieve: true,
                    bLengthChange: true,
                    responsive: false,
                    scrollX: true,
                    scrollCollapse: true,
                headerCallback: function(thead, data, start, end, display) {
                    if (data?.length == 0) {
                        $(thead).parent().parent().parent().addClass("width-full")
                        $(thead).parent().parent().addClass("width-full")
                    }
                },
                    ajax: "{{route('adminRoleList')}}",
                    autoWidth: false,
                    language: {
                        paginate: {
                            next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                            previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                        }
                    },
                    columns: [
                        {"data": "title", "orderable": false},
                        {"data": "activity", "orderable": false},
                    ],
                });

            }
            var table_route = $('#table_route').DataTable({
                processing: true,
                pageLength: 10,
                bLengthChange: true,
                responsive: false,
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                headerCallback: function(thead, data, start, end, display) {
                    if (data?.length == 0) {
                        $(thead).parent().parent().parent().addClass("width-full")
                        $(thead).parent().parent().addClass("width-full")
                    }
                },
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
            });

            @if(isset($id))
            var roleActionTable = $('#role_permission').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 10,
                retrieve: true,
                bLengthChange: true,
                responsive: false,
                ajax: "{{route('adminRolePermissionGroupList',['id'=>encrypt($id??0)])}}",
                autoWidth: false,
                scrollX: true,
                scrollCollapse: true,
                headerCallback: function(thead, data, start, end, display) {
                    if (data?.length == 0) {
                        $(thead).parent().parent().parent().addClass("width-full")
                        $(thead).parent().parent().addClass("width-full")
                    }
                },
                language: {
                    paginate: {
                        next: '<i class="fa fa-angle-double-right" aria-hidden="true"></i>',
                        previous: '<i class="fa fa-angle-double-left" aria-hidden="true"></i>'
                    }
                },
                columns: [
                    {"data": "checkbox", "orderable": false},
                    {"data": "action"},
                    {"data": "for"},
                    {"data": "route"},
                    {"data": "group"},
                ]
            });
            $('#role_permission').on( 'draw.dt', function () {
                var a = document.querySelectorAll('#role_permission tbody .role_checkbox');
                var b = document.querySelector('#role_permission thead .role_checkbox');
                let z = true;
                a.forEach(e => {
                    if(!e.checked)
                        z = false;
                });
                if(z)
                    b.checked = true;
                else
                    b.checked = false;
            } );
            function saveRolePermissionRequest(data){
                let d = document.querySelector('#role_permission input[id="allSelect"]');
                $.post("{{ route('adminRolePermissionSave') }}",data,(response)=>{
                    if(response.data.all) d.checked = true;
                    else d.checked = false;

                    var a = document.querySelectorAll('#role_permission tbody .role_checkbox');

                    a.forEach(e => {
                        if(isNaN(data.id) && data.id.toString() == "NaN"){
                            if(!d.checked)
                                e.checked = false;
                            else
                                e.checked = true;

                        }else{
                            if(d.checked)
                                e.checked = true;

                        }

                    });



                });
            }
            $(document.body).on('click', '.role_checkbox', function () {
                var id = $(this).data('id');
                var group = $("#role_filter").val();
                let data = {
                    _token: '{{ csrf_token() }}',
                    role_id: $('#role_id').val(),
                    id: id,
                    group: group,
                };
                saveRolePermissionRequest(data);
                // if(group != 'all'){
                //     var a = document.querySelectorAll('#role_permission tbody .role_checkbox');
                //     var b = document.querySelector('#role_permission thead .role_checkbox');
                //     let z = true;
                //     a.forEach(e => {
                //         if(!e.checked)
                //             z = false;
                //     });
                // }
            });
            $(document.body).on('change', '#role_filter', function () {
                var value = $(this).val();
                if(value == 'all')
                    roleActionTable.columns( 4 ).search( "" ).draw();
                else{
                    roleActionTable.columns( 4 ).search("(^"+value+"$)",true,false).draw();
                }
            });
           @endif

            $(document.body).on('click', '.nav-link', function () {
                var id = $(this).data('id');
                if (id != 'undefined') {
                    $('#table').DataTable().destroy();
                    getTable(id)
                    
                }

            });

            getTable('active_users');

        })(jQuery)
