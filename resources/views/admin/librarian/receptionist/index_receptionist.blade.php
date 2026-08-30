@extends('admin..layout.master')
@section('admin.')
    <!-- START PAGE WRAPPER -->
<div class="page-wrapper">
			<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Admin</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href=""><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page"><a href="">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View Categories</li>
							</ol>
						</nav>
					</div>
					
				</div>
				<!--end breadcrumb-->
                <!-- I AM ADDING HERE #1 ANCOR TAG FOR MAKING ADD BUTTON -->
                 <a href="" class="btn btn-success">Add Category</a>
                 <hr/>
				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
                    <table id="example" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th><input type="checkbox" name="" id="checkAll[]"></th>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Discription</th>
                                <th>Status</th>
                                <th>Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                           
                                
                            <tr>
                                <td><input type="checkbox" name="statusAll[]" id=""></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td> 

                                    
                                    <button class="btn btn-success btn-sm singleStatus"><i class="fa fa-thumbs-up"></i></button>
                                    
                                    <button class="btn btn-danger btn-sm singleStatus"><i class="fa fa-thumbs-down"></i></button>
                                    
                               </td>
                                <td style=" font-size:16px;">
                                    <a href="" style="color: #15CA20;"><i class="lni lni-eye me-3"></i></a>
                                    <a href="" style="color: #053aad;"><i class="lni lni-pencil me-3"></i></a>
                                    <a href="" id="delete" style="color: #ff0000;"><i class="lni lni-trash"></i></a>
                                </td>
                            </tr>
                            
                           
                        </tbody>
                        <tfoot>
                            <tr>
                                <th><input type="checkbox" name="" id="checkAll[]"></th>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Discription</th>
                                <th>Status</th>
                                <th>Manage</th>
                            </tr>
                        </tfoot>
                    </table>
						</div>
					</div>
				</div>
				<hr/>
			</div>
		</div>
        <!-- END PAGE WRAPPER -->
@endsection