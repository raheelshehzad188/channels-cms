
                <button class="smallDeviceMenuController md:hidden block leading-0">
                  <iconify-icon class="cursor-pointer text-slate-900 dark:text-white text-2xl" icon="heroicons-outline:menu-alt-3"></iconify-icon>
                </button>
                <!-- end mobile menu -->
              </div>
              <!-- end nav tools -->
            </div>
          </div>
        </div>

        <!-- BEGIN: Search Modal -->
        <div class="modal fade fixed top-0 left-0 hidden w-full h-full outline-none overflow-x-hidden overflow-y-auto" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
          <div class="modal-dialog relative w-auto pointer-events-none top-1/4">
            <div class="modal-content border-none shadow-lg relative flex flex-col w-full pointer-events-auto bg-white dark:bg-slate-900 bg-clip-padding rounded-md outline-none text-current">
              <form>
                <div class="relative">
                  <input type="text" class="form-control !py-3 !pr-12" placeholder="Search">
                  <button class="absolute right-0 top-1/2 -translate-y-1/2 w-9 h-full border-l text-xl border-l-slate-200 dark:border-l-slate-600 dark:text-slate-300 flex items-center justify-center">
                    <iconify-icon icon="heroicons-solid:search"></iconify-icon>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <!-- END: Search Modal -->
        <!-- END: Header -->
        <!-- END: Header -->
        <div class="content-wrapper transition-all duration-150 ltr:ml-[248px] rtl:mr-[248px]" id="content_wrapper">
          <div class="page-content">
            <div class="transition-all duration-150 container-fluid" id="page_layout">
              <div id="content_layout">

                <div>
                  <div class="flex justify-between flex-wrap items-center mb-6">
                    <h4 class="font-medium lg:text-2xl text-xl capitalize text-slate-900 inline-block ltr:pr-4 rtl:pl-4 mb-4 sm:mb-0 flex space-x-3 rtl:space-x-reverse">Dashboard</h4>
                    <div class="flex sm:space-x-4 space-x-2 sm:justify-end items-center rtl:space-x-reverse">
                      <button class="btn leading-0 inline-flex justify-center bg-white text-slate-700 dark:bg-slate-800 dark:text-slate-300 !font-normal">
                        <span class="flex items-center">
                        <iconify-icon class="text-xl ltr:mr-2 rtl:ml-2 font-light" icon="heroicons-outline:calendar"></iconify-icon>
                        <span>Weekly</span>
                        </span>
                      </button>
                      <button class="btn leading-0 inline-flex justify-center bg-white text-slate-700 dark:bg-slate-800 dark:text-slate-300 !font-normal">
                        <span class="flex items-center">
                        <iconify-icon class="text-xl ltr:mr-2 rtl:ml-2 font-light" icon="heroicons-outline:filter"></iconify-icon>
                        <span>Select Date</span>
                        </span>
                      </button>
                    </div>
                  </div>
                  <div class="grid grid-cols-12 gap-5 mb-5">
                    <div class="2xl:col-span-3 lg:col-span-4 col-span-12">
                      <div class="bg-no-repeat bg-cover bg-center p-4 rounded-[6px] relative" style="background-image: url(<?= $assets ?>assets/images/all-img/widget-bg-1.png)">
                        <div class="max-w-[180px]">
                          <div class="text-xl font-medium text-slate-900 mb-2">
                            Upgrade your Dashcode
                          </div>
                          <p class="text-sm text-slate-800">
                            Pro plan for better results
                          </p>
                        </div>
                        <div class="absolute top-1/2 -translate-y-1/2 ltr:right-6 rtl:left-6 mt-2 h-12 w-12 bg-white rounded-full text-xs font-medium
                            flex flex-col items-center justify-center">
                          Now
                        </div>
                      </div>
                    </div>
                    <div class="2xl:col-span-9 lg:col-span-8 col-span-12">
                      <div class="p-4 card">
                        <div class="grid md:grid-cols-3 col-span-1 gap-4">

                          <!-- BEGIN: Group Chart2 -->


                          <div class="py-[18px] px-4 rounded-[6px] bg-[#E5F9FF] dark:bg-slate-900    ">
                            <div class="flex items-center space-x-6 rtl:space-x-reverse">
                              <div class="flex-none">
                                <div id="wline1"></div>
                              </div>
                              <div class="flex-1">
                                <div class="text-slate-800 dark:text-slate-300 text-sm mb-1 font-medium">
                                  Totel revenue
                                </div>
                                <div class="text-slate-900 dark:text-white text-lg font-medium">
                                  3,564
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="py-[18px] px-4 rounded-[6px] bg-[#FFEDE5] dark:bg-slate-900    ">
                            <div class="flex items-center space-x-6 rtl:space-x-reverse">
                              <div class="flex-none">
                                <div id="wline2"></div>
                              </div>
                              <div class="flex-1">
                                <div class="text-slate-800 dark:text-slate-300 text-sm mb-1 font-medium">
                                  Products sold
                                </div>
                                <div class="text-slate-900 dark:text-white text-lg font-medium">
                                  564
                                </div>
                              </div>
                            </div>
                          </div>

                          <div class="py-[18px] px-4 rounded-[6px] bg-[#EAE5FF] dark:bg-slate-900    ">
                            <div class="flex items-center space-x-6 rtl:space-x-reverse">
                              <div class="flex-none">
                                <div id="wline3"></div>
                              </div>
                              <div class="flex-1">
                                <div class="text-slate-800 dark:text-slate-300 text-sm mb-1 font-medium">
                                  Growth
                                </div>
                                <div class="text-slate-900 dark:text-white text-lg font-medium">
                                  +5.0%
                                </div>
                              </div>
                            </div>
                          </div>

                          <!-- END: Group Chart2 -->
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="grid grid-cols-12 gap-5">
                    <div class="lg:col-span-8 col-span-12">
                      <div class="card">
                        <div class="card-body p-6">
                          <div class="legend-ring">
                            <div id="revenue-barchart"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="lg:col-span-4 col-span-12">
                      <div class="card">
                        <header class="card-header">
                          <h4 class="card-title">Overview</h4>
                          <div>
                            <!-- BEGIN: Card Dropdown -->
                            <div class="relative">
                              <div class="dropdown relative">
                                <button class="text-xl text-center block w-full " type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                  <span class="text-lg inline-flex h-6 w-6 flex-col items-center justify-center border border-slate-200 dark:border-slate-700
                    rounded dark:text-slate-400">
                <iconify-icon icon="heroicons-outline:dots-horizontal"></iconify-icon>
            </span>
                                </button>
                                <ul class=" dropdown-menu min-w-[120px] absolute text-sm text-slate-700 dark:text-white hidden bg-white dark:bg-slate-700
                shadow z-[2] overflow-hidden list-none text-left rounded-lg mt-1 m-0 bg-clip-padding border-none">
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last 28 Days</a>
                                  </li>
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last Month</a>
                                  </li>
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last Year</a>
                                  </li>
                                </ul>
                              </div>
                            </div>
                            <!-- END: Card Droopdown -->
                          </div>
                        </header>
                        <div class="card-body p-6">
                          <div id="radial-bar"></div>
                        </div>
                      </div>
                    </div>
                    <div class="lg:col-span-8 col-span-12">
                      <div class="card">
                        <header class="card-header noborder">
                          <h4 class="card-title">All Company</h4>
                          <div>
                            <!-- BEGIN: Card Dropdown -->
                            <div class="relative">
                              <div class="dropdown relative">
                                <button class="text-xl text-center block w-full " type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                  <span class="text-lg inline-flex h-6 w-6 flex-col items-center justify-center border border-slate-200 dark:border-slate-700
                    rounded dark:text-slate-400">
                <iconify-icon icon="heroicons-outline:dots-horizontal"></iconify-icon>
            </span>
                                </button>
                                <ul class=" dropdown-menu min-w-[120px] absolute text-sm text-slate-700 dark:text-white hidden bg-white dark:bg-slate-700
                shadow z-[2] overflow-hidden list-none text-left rounded-lg mt-1 m-0 bg-clip-padding border-none">
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last 28 Days</a>
                                  </li>
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last Month</a>
                                  </li>
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last Year</a>
                                  </li>
                                </ul>
                              </div>
                            </div>
                            <!-- END: Card Droopdown -->
                          </div>
                        </header>
                        <div class="card-body p-6">

                          <!-- BEGIN: Company Table -->


                          <div class="overflow-x-auto -mx-6">
                            <div class="inline-block min-w-full align-middle">
                              <div class="overflow-hidden ">
                                <table class="min-w-full divide-y divide-slate-100 table-fixed dark:divide-slate-700">
                                  <thead class="  bg-slate-200 dark:bg-slate-700">
                                    <tr>

                                      <th scope="col" class=" table-th ">
                                        Company
                                      </th>

                                      <th scope="col" class=" table-th ">
                                        Category
                                      </th>

                                      <th scope="col" class=" table-th ">
                                        Sales
                                      </th>

                                      <th scope="col" class=" table-th ">
                                        Views
                                      </th>

                                      <th scope="col" class=" table-th ">
                                        revenue
                                      </th>

                                    </tr>
                                  </thead>
                                  <tbody class="bg-white divide-y divide-slate-100 dark:bg-slate-800 dark:divide-slate-700">

                                    <tr>
                                      <td class="table-td">
                                        <div class="flex items-center">
                                          <div class="flex-none">
                                            <div class="w-8 h-8 rounded-[100%] ltr:mr-3 rtl:ml-3">
                                              <img src=assets/images/users/user-1.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                            </div>
                                          </div>
                                          <div class="flex-1 text-start">
                                            <h4 class="text-sm font-medium text-slate-600 whitespace-nowrap">
                                              Biffco Enterprises Ltd.
                                            </h4>
                                            <div class="text-xs font-normal text-slate-600 dark:text-slate-400">
                                              Biffco@example.com
                                            </div>
                                          </div>
                                        </div>
                                      </td>
                                      <td class="table-td">Technology</td>
                                      <td class="table-td ">
                                        <div class="flex space-x-6 items-center rtl:space-x-reverse">
                                          <span>
                                        95%</span>
                                          <span class=" text-xl  text-danger-500 ">

                                            <iconify-icon icon="heroicons-outline:trending-down"></iconify-icon>
                                        
                                    </span>
                                        </div>
                                      </td>
                                      <td class="table-td ">343</td>
                                      <td class="table-td ">$231.26</td>
                                    </tr>

                                    <tr>
                                      <td class="table-td">
                                        <div class="flex items-center">
                                          <div class="flex-none">
                                            <div class="w-8 h-8 rounded-[100%] ltr:mr-3 rtl:ml-3">
                                              <img src=assets/images/users/user-2.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                            </div>
                                          </div>
                                          <div class="flex-1 text-start">
                                            <h4 class="text-sm font-medium text-slate-600 whitespace-nowrap">
                                              Biffco Enterprises Ltd.
                                            </h4>
                                            <div class="text-xs font-normal text-slate-600 dark:text-slate-400">
                                              Biffco@example.com
                                            </div>
                                          </div>
                                        </div>
                                      </td>
                                      <td class="table-td">Technology</td>
                                      <td class="table-td ">
                                        <div class="flex space-x-6 items-center rtl:space-x-reverse">
                                          <span>
                                        197%</span>
                                          <span class=" text-xl  text-success-500 ">

                                            <iconify-icon icon="heroicons-outline:trending-up"></iconify-icon>
                                        
                                    </span>
                                        </div>
                                      </td>
                                      <td class="table-td ">315</td>
                                      <td class="table-td ">$432.81</td>
                                    </tr>

                                    <tr>
                                      <td class="table-td">
                                        <div class="flex items-center">
                                          <div class="flex-none">
                                            <div class="w-8 h-8 rounded-[100%] ltr:mr-3 rtl:ml-3">
                                              <img src=assets/images/users/user-3.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                            </div>
                                          </div>
                                          <div class="flex-1 text-start">
                                            <h4 class="text-sm font-medium text-slate-600 whitespace-nowrap">
                                              Biffco Enterprises Ltd.
                                            </h4>
                                            <div class="text-xs font-normal text-slate-600 dark:text-slate-400">
                                              Biffco@example.com
                                            </div>
                                          </div>
                                        </div>
                                      </td>
                                      <td class="table-td">Technology</td>
                                      <td class="table-td ">
                                        <div class="flex space-x-6 items-center rtl:space-x-reverse">
                                          <span>
                                        137%</span>
                                          <span class=" text-xl  text-success-500 ">

                                            <iconify-icon icon="heroicons-outline:trending-up"></iconify-icon>
                                        
                                    </span>
                                        </div>
                                      </td>
                                      <td class="table-td ">329</td>
                                      <td class="table-td ">$437.65</td>
                                    </tr>

                                    <tr>
                                      <td class="table-td">
                                        <div class="flex items-center">
                                          <div class="flex-none">
                                            <div class="w-8 h-8 rounded-[100%] ltr:mr-3 rtl:ml-3">
                                              <img src=assets/images/users/user-4.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                            </div>
                                          </div>
                                          <div class="flex-1 text-start">
                                            <h4 class="text-sm font-medium text-slate-600 whitespace-nowrap">
                                              Biffco Enterprises Ltd.
                                            </h4>
                                            <div class="text-xs font-normal text-slate-600 dark:text-slate-400">
                                              Biffco@example.com
                                            </div>
                                          </div>
                                        </div>
                                      </td>
                                      <td class="table-td">Technology</td>
                                      <td class="table-td ">
                                        <div class="flex space-x-6 items-center rtl:space-x-reverse">
                                          <span>
                                        101%</span>
                                          <span class=" text-xl  text-success-500 ">

                                            <iconify-icon icon="heroicons-outline:trending-up"></iconify-icon>
                                        
                                    </span>
                                        </div>
                                      </td>
                                      <td class="table-td ">336</td>
                                      <td class="table-td ">$387.55</td>
                                    </tr>

                                    <tr>
                                      <td class="table-td">
                                        <div class="flex items-center">
                                          <div class="flex-none">
                                            <div class="w-8 h-8 rounded-[100%] ltr:mr-3 rtl:ml-3">
                                              <img src=assets/images/users/user-5.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                            </div>
                                          </div>
                                          <div class="flex-1 text-start">
                                            <h4 class="text-sm font-medium text-slate-600 whitespace-nowrap">
                                              Biffco Enterprises Ltd.
                                            </h4>
                                            <div class="text-xs font-normal text-slate-600 dark:text-slate-400">
                                              Biffco@example.com
                                            </div>
                                          </div>
                                        </div>
                                      </td>
                                      <td class="table-td">Technology</td>
                                      <td class="table-td ">
                                        <div class="flex space-x-6 items-center rtl:space-x-reverse">
                                          <span>
                                        99%</span>
                                          <span class=" text-xl  text-danger-500 ">

                                            <iconify-icon icon="heroicons-outline:trending-down"></iconify-icon>
                                        
                                    </span>
                                        </div>
                                      </td>
                                      <td class="table-td ">375</td>
                                      <td class="table-td ">$489.80</td>
                                    </tr>

                                    <tr>
                                      <td class="table-td">
                                        <div class="flex items-center">
                                          <div class="flex-none">
                                            <div class="w-8 h-8 rounded-[100%] ltr:mr-3 rtl:ml-3">
                                              <img src=assets/images/users/user-6.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                            </div>
                                          </div>
                                          <div class="flex-1 text-start">
                                            <h4 class="text-sm font-medium text-slate-600 whitespace-nowrap">
                                              Biffco Enterprises Ltd.
                                            </h4>
                                            <div class="text-xs font-normal text-slate-600 dark:text-slate-400">
                                              Biffco@example.com
                                            </div>
                                          </div>
                                        </div>
                                      </td>
                                      <td class="table-td">Technology</td>
                                      <td class="table-td ">
                                        <div class="flex space-x-6 items-center rtl:space-x-reverse">
                                          <span>
                                        101%</span>
                                          <span class=" text-xl  text-success-500 ">

                                            <iconify-icon icon="heroicons-outline:trending-up"></iconify-icon>
                                        
                                    </span>
                                        </div>
                                      </td>
                                      <td class="table-td ">490</td>
                                      <td class="table-td ">$421.45</td>
                                    </tr>

                                  </tbody>
                                </table>
                              </div>
                            </div>
                          </div>
                          <!-- END: Company Table -->
                        </div>
                      </div>
                    </div>
                    <div class="lg:col-span-4 col-span-12">
                      <div class="card ">
                        <div class="card-header ">
                          <h4 class="card-title">Recent Activity</h4>
                          <div>
                            <!-- BEGIN: Card Dropdown -->
                            <div class="relative">
                              <div class="dropdown relative">
                                <button class="text-xl text-center block w-full " type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                  <span class="text-lg inline-flex h-6 w-6 flex-col items-center justify-center border border-slate-200 dark:border-slate-700
                    rounded dark:text-slate-400">
                <iconify-icon icon="heroicons-outline:dots-horizontal"></iconify-icon>
            </span>
                                </button>
                                <ul class=" dropdown-menu min-w-[120px] absolute text-sm text-slate-700 dark:text-white hidden bg-white dark:bg-slate-700
                shadow z-[2] overflow-hidden list-none text-left rounded-lg mt-1 m-0 bg-clip-padding border-none">
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last 28 Days</a>
                                  </li>
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last Month</a>
                                  </li>
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last Year</a>
                                  </li>
                                </ul>
                              </div>
                            </div>
                            <!-- END: Card Droopdown -->
                          </div>
                        </div>
                        <div class="card-body p-6">

                          <!-- BEGIN: Activity Card -->

                          <div>
                            <ul class="list-item space-y-3 h-full overflow-x-auto">



                              <li class="flex items-center space-x-3 rtl:space-x-reverse border-b border-slate-100 dark:border-slate-700 last:border-b-0 pb-3 last:pb-0">
                                <div>
                                  <div class="w-8 h-8 rounded-[100%]">
                                    <img src=assets/images/users/user-1.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                  </div>
                                </div>
                                <div class="text-start overflow-hidden text-ellipsis whitespace-nowrap max-w-[63%]">
                                  <div class="text-sm text-slate-600 dark:text-slate-300 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Finance KPI Mobile app launch preparion meeting.
                                  </div>
                                </div>
                                <div class="flex-1 ltr:text-right rtl:text-left">
                                  <div class="text-sm font-light text-slate-400 dark:text-slate-400">
                                    1 hours
                                  </div>
                                </div>
                              </li>


                              <li class="flex items-center space-x-3 rtl:space-x-reverse border-b border-slate-100 dark:border-slate-700 last:border-b-0 pb-3 last:pb-0">
                                <div>
                                  <div class="w-8 h-8 rounded-[100%]">
                                    <img src=assets/images/users/user-2.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                  </div>
                                </div>
                                <div class="text-start overflow-hidden text-ellipsis whitespace-nowrap max-w-[63%]">
                                  <div class="text-sm text-slate-600 dark:text-slate-300 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Finance KPI Mobile app launch preparion meeting.
                                  </div>
                                </div>
                                <div class="flex-1 ltr:text-right rtl:text-left">
                                  <div class="text-sm font-light text-slate-400 dark:text-slate-400">
                                    1 hours
                                  </div>
                                </div>
                              </li>


                              <li class="flex items-center space-x-3 rtl:space-x-reverse border-b border-slate-100 dark:border-slate-700 last:border-b-0 pb-3 last:pb-0">
                                <div>
                                  <div class="w-8 h-8 rounded-[100%]">
                                    <img src=assets/images/users/user-3.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                  </div>
                                </div>
                                <div class="text-start overflow-hidden text-ellipsis whitespace-nowrap max-w-[63%]">
                                  <div class="text-sm text-slate-600 dark:text-slate-300 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Finance KPI Mobile app launch preparion meeting.
                                  </div>
                                </div>
                                <div class="flex-1 ltr:text-right rtl:text-left">
                                  <div class="text-sm font-light text-slate-400 dark:text-slate-400">
                                    1 hours
                                  </div>
                                </div>
                              </li>


                              <li class="flex items-center space-x-3 rtl:space-x-reverse border-b border-slate-100 dark:border-slate-700 last:border-b-0 pb-3 last:pb-0">
                                <div>
                                  <div class="w-8 h-8 rounded-[100%]">
                                    <img src=assets/images/users/user-4.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                  </div>
                                </div>
                                <div class="text-start overflow-hidden text-ellipsis whitespace-nowrap max-w-[63%]">
                                  <div class="text-sm text-slate-600 dark:text-slate-300 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Finance KPI Mobile app launch preparion meeting.
                                  </div>
                                </div>
                                <div class="flex-1 ltr:text-right rtl:text-left">
                                  <div class="text-sm font-light text-slate-400 dark:text-slate-400">
                                    1 hours
                                  </div>
                                </div>
                              </li>


                              <li class="flex items-center space-x-3 rtl:space-x-reverse border-b border-slate-100 dark:border-slate-700 last:border-b-0 pb-3 last:pb-0">
                                <div>
                                  <div class="w-8 h-8 rounded-[100%]">
                                    <img src=assets/images/users/user-5.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                  </div>
                                </div>
                                <div class="text-start overflow-hidden text-ellipsis whitespace-nowrap max-w-[63%]">
                                  <div class="text-sm text-slate-600 dark:text-slate-300 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Finance KPI Mobile app launch preparion meeting.
                                  </div>
                                </div>
                                <div class="flex-1 ltr:text-right rtl:text-left">
                                  <div class="text-sm font-light text-slate-400 dark:text-slate-400">
                                    1 hours
                                  </div>
                                </div>
                              </li>


                              <li class="flex items-center space-x-3 rtl:space-x-reverse border-b border-slate-100 dark:border-slate-700 last:border-b-0 pb-3 last:pb-0">
                                <div>
                                  <div class="w-8 h-8 rounded-[100%]">
                                    <img src=assets/images/users/user-6.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                  </div>
                                </div>
                                <div class="text-start overflow-hidden text-ellipsis whitespace-nowrap max-w-[63%]">
                                  <div class="text-sm text-slate-600 dark:text-slate-300 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Finance KPI Mobile app launch preparion meeting.
                                  </div>
                                </div>
                                <div class="flex-1 ltr:text-right rtl:text-left">
                                  <div class="text-sm font-light text-slate-400 dark:text-slate-400">
                                    1 hours
                                  </div>
                                </div>
                              </li>


                              <li class="flex items-center space-x-3 rtl:space-x-reverse border-b border-slate-100 dark:border-slate-700 last:border-b-0 pb-3 last:pb-0">
                                <div>
                                  <div class="w-8 h-8 rounded-[100%]">
                                    <img src=assets/images/users/user-1.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                  </div>
                                </div>
                                <div class="text-start overflow-hidden text-ellipsis whitespace-nowrap max-w-[63%]">
                                  <div class="text-sm text-slate-600 dark:text-slate-300 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Finance KPI Mobile app launch preparion meeting.
                                  </div>
                                </div>
                                <div class="flex-1 ltr:text-right rtl:text-left">
                                  <div class="text-sm font-light text-slate-400 dark:text-slate-400">
                                    1 hours
                                  </div>
                                </div>
                              </li>


                              <li class="flex items-center space-x-3 rtl:space-x-reverse border-b border-slate-100 dark:border-slate-700 last:border-b-0 pb-3 last:pb-0">
                                <div>
                                  <div class="w-8 h-8 rounded-[100%]">
                                    <img src=assets/images/users/user-2.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                  </div>
                                </div>
                                <div class="text-start overflow-hidden text-ellipsis whitespace-nowrap max-w-[63%]">
                                  <div class="text-sm text-slate-600 dark:text-slate-300 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Finance KPI Mobile app launch preparion meeting.
                                  </div>
                                </div>
                                <div class="flex-1 ltr:text-right rtl:text-left">
                                  <div class="text-sm font-light text-slate-400 dark:text-slate-400">
                                    1 hours
                                  </div>
                                </div>
                              </li>


                              <li class="flex items-center space-x-3 rtl:space-x-reverse border-b border-slate-100 dark:border-slate-700 last:border-b-0 pb-3 last:pb-0">
                                <div>
                                  <div class="w-8 h-8 rounded-[100%]">
                                    <img src=assets/images/users/user-3.jpg alt="" class="w-full h-full rounded-[100%] object-cover">
                                  </div>
                                </div>
                                <div class="text-start overflow-hidden text-ellipsis whitespace-nowrap max-w-[63%]">
                                  <div class="text-sm text-slate-600 dark:text-slate-300 overflow-hidden text-ellipsis whitespace-nowrap">
                                    Finance KPI Mobile app launch preparion meeting.
                                  </div>
                                </div>
                                <div class="flex-1 ltr:text-right rtl:text-left">
                                  <div class="text-sm font-light text-slate-400 dark:text-slate-400">
                                    1 hours
                                  </div>
                                </div>
                              </li>

                            </ul>
                          </div>
                          <!-- END: Activity Card -->



                        </div>
                      </div>
                    </div>
                    <div class="lg:col-span-8 col-span-12">
                      <div class="card">
                        <div class="card-header">
                          <h4 class="card-title">Most Sales</h4>
                          <div>
                            <!-- BEGIN: Card Dropdown -->
                            <div class="relative">
                              <div class="dropdown relative">
                                <button class="text-xl text-center block w-full " type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                  <span class="text-lg inline-flex h-6 w-6 flex-col items-center justify-center border border-slate-200 dark:border-slate-700
                    rounded dark:text-slate-400">
                <iconify-icon icon="heroicons-outline:dots-horizontal"></iconify-icon>
            </span>
                                </button>
                                <ul class=" dropdown-menu min-w-[120px] absolute text-sm text-slate-700 dark:text-white hidden bg-white dark:bg-slate-700
                shadow z-[2] overflow-hidden list-none text-left rounded-lg mt-1 m-0 bg-clip-padding border-none">
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last 28 Days</a>
                                  </li>
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last Month</a>
                                  </li>
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last Year</a>
                                  </li>
                                </ul>
                              </div>
                            </div>
                            <!-- END: Card Droopdown -->
                          </div>
                        </div>
                        <div class="card-body p-6">

                          <!-- BEGIN: Most Sale -->



                          <div class="md:flex items-center">
                            <div class="grow-0">
                              <h4 class="text-slate-600 dark:text-slate-200 text-sm font-normal mb-[6px]">
                                Total earnings
                              </h4>

                              <div class="text-lg font-medium mb-[6px] dark:text-white text-slate-900">
                                $12,65,64787.00
                              </div>


                              <div class="text-xs font-light dark:text-slate-200">
                                <span class="text-primary-500">+08%</span> From last month
                              </div>
                              <ul class="bg-slate-50 dark:bg-slate-900 rounded p-4 min-w-[184px] space-y-5 mt-4">



                                <li class="flex justify-between text-xs text-slate-600 dark:text-slate-300">
                                  <span class="flex space-x-2 rtl:space-x-reverse items-center">
                <span
                  class="inline-flex h-[6px] w-[6px] bg-primary-500 ring-opacity-25 rounded-full ring-4
                        bg-primary-500 ring-primary-500
                        "
                ></span>
                                  <span>Nevada</span>
                                  </span>
                                  <span>$125k</span>
                                </li>


                                <li class="flex justify-between text-xs text-slate-600 dark:text-slate-300">
                                  <span class="flex space-x-2 rtl:space-x-reverse items-center">
                <span
                  class="inline-flex h-[6px] w-[6px] bg-primary-500 ring-opacity-25 rounded-full ring-4
                        bg-success-500 ring-success-500
                        "
                ></span>
                                  <span>Colorado</span>
                                  </span>
                                  <span>$$325k</span>
                                </li>


                                <li class="flex justify-between text-xs text-slate-600 dark:text-slate-300">
                                  <span class="flex space-x-2 rtl:space-x-reverse items-center">
                <span
                  class="inline-flex h-[6px] w-[6px] bg-primary-500 ring-opacity-25 rounded-full ring-4
                        bg-info-500 ring-info-500
                        "
                ></span>
                                  <span>Iowa</span>
                                  </span>
                                  <span>$67</span>
                                </li>


                                <li class="flex justify-between text-xs text-slate-600 dark:text-slate-300">
                                  <span class="flex space-x-2 rtl:space-x-reverse items-center">
                <span
                  class="inline-flex h-[6px] w-[6px] bg-primary-500 ring-opacity-25 rounded-full ring-4
                        bg-warning-500 ring-warning-500
                        "
                ></span>
                                  <span>Arkansas</span>
                                  </span>
                                  <span>$354k</span>
                                </li>


                                <li class="flex justify-between text-xs text-slate-600 dark:text-slate-300">
                                  <span class="flex space-x-2 rtl:space-x-reverse items-center">
                <span
                  class="inline-flex h-[6px] w-[6px] bg-primary-500 ring-opacity-25 rounded-full ring-4
                        bg-success-500 ring-success-500
                        "
                ></span>
                                  <span>Wyoming</span>
                                  </span>
                                  <span>$195k</span>
                                </li>


                                <li class="flex justify-between text-xs text-slate-600 dark:text-slate-300">
                                  <span class="flex space-x-2 rtl:space-x-reverse items-center">
                <span
                  class="inline-flex h-[6px] w-[6px] bg-primary-500 ring-opacity-25 rounded-full ring-4
                        bg-secondary-500 ring-secondary-500
                        "
                ></span>
                                  <span>Other countries</span>
                                  </span>
                                  <span>$295k</span>
                                </li>

                              </ul>
                            </div>
                            <div class=" grow">
                              <div class="h-[360px] w-full bg-white dark:bg-slate-800 ltr:pl-10 rtl:pr-10">
                                <div id="world-map" class="h-full w-full"></div>
                              </div>
                            </div>
                          </div>
                          <!-- END: Most Sale -->
                        </div>
                      </div>
                    </div>
                    <div class="lg:col-span-4 col-span-12">
                      <div class="card">
                        <div class="card-header">
                          <h4 class="card-title">Overview</h4>
                          <div>
                            <!-- BEGIN: Card Dropdown -->
                            <div class="relative">
                              <div class="dropdown relative">
                                <button class="text-xl text-center block w-full " type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                  <span class="text-lg inline-flex h-6 w-6 flex-col items-center justify-center border border-slate-200 dark:border-slate-700
                    rounded dark:text-slate-400">
                <iconify-icon icon="heroicons-outline:dots-horizontal"></iconify-icon>
            </span>
                                </button>
                                <ul class=" dropdown-menu min-w-[120px] absolute text-sm text-slate-700 dark:text-white hidden bg-white dark:bg-slate-700
                shadow z-[2] overflow-hidden list-none text-left rounded-lg mt-1 m-0 bg-clip-padding border-none">
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last 28 Days</a>
                                  </li>
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last Month</a>
                                  </li>
                                  <li>
                                    <a href="#" class="text-slate-600 dark:text-white block font-Inter font-normal px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-600
                        dark:hover:text-white">
                                      Last Year</a>
                                  </li>
                                </ul>
                              </div>
                            </div>
                            <!-- END: Card Droopdown -->
                          </div>
                        </div>
                        <div class="card-body p-6">
                          <div id="radar-home-chart"></div>
                          <div class="bg-slate-50 dark:bg-slate-900 rounded p-4 mt-8 flex justify-between flex-wrap">
                            <div class="space-y-1">
                              <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">
                                Invested amount
                              </h4>
                              <div class="text-sm font-medium text-slate-900 dark:text-white">
                                $8264.35
                              </div>
                              <div class="text-slate-500 dark:text-slate-300 text-xs font-normal">
                                +0.001.23 (0.2%)
                              </div>
                            </div>
                            <div class="space-y-1">
                              <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">
                                Invested amount
                              </h4>
                              <div class="text-sm font-medium text-slate-900 dark:text-white">
                                $8264.35
                              </div>
                            </div>
                            <div class="space-y-1">
                              <h4 class="text-slate-600 dark:text-slate-200 text-xs font-normal">
                                Invested amount
                              </h4>
                              <div class="text-sm font-medium text-slate-900 dark:text-white">
                                $8264.35
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- BEGIN: Footer For Desktop and tab -->
      <footer class="md:block hidden" id="footer">
        <div class="site-footer px-6 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-300 py-4 ltr:ml-[248px] rtl:mr-[248px]">
          <div class="grid md:grid-cols-2 grid-cols-1 md:gap-5">
            <div class="text-center ltr:md:text-start rtl:md:text-right text-sm">
              COPYRIGHT ©
              <span id="thisYear"></span>
              DashCode, All rights Reserved
            </div>
            <div class="ltr:md:text-right rtl:md:text-end text-center text-sm">
              Hand-crafted &amp; Made by
              <a href="https://codeshaper.net" target="_blank" class="text-primary-500 font-semibold">
                Codeshaper
              </a>
            </div>
          </div>
        </div>
      </footer>
      <!-- END: Footer For Desktop and tab -->

      <div class="bg-white bg-no-repeat custom-dropshadow footer-bg dark:bg-slate-700 flex justify-around items-center
    backdrop-filter backdrop-blur-[40px] fixed left-0 bottom-0 w-full z-[9999] bothrefm-0 py-[12px] px-4 md:hidden">
        <a href="chat.html">
          <div>
            <span class="relative cursor-pointer rounded-full text-[20px] flex flex-col items-center justify-center mb-1 dark:text-white
          text-slate-900 ">
        <iconify-icon icon="heroicons-outline:mail"></iconify-icon>
        <span class="absolute right-[5px] lg:hrefp-0 -hrefp-2 h-4 w-4 bg-red-500 text-[8px] font-semibold flex flex-col items-center
            justify-center rounded-full text-white z-[99]">
          10
        </span>
            </span>
            <span class="block text-[11px] text-slate-600 dark:text-slate-300">
        Messages
      </span>
          </div>
        </a>
        <a href="profile.html" class="relative bg-white bg-no-repeat backdrop-filter backdrop-blur-[40px] rounded-full footer-bg dark:bg-slate-700
      h-[65px] w-[65px] z-[-1] -mt-[40px] flex justify-center items-center">
          <div class="h-[50px] w-[50px] rounded-full relative left-[0px] hrefp-[0px] custom-dropshadow">
            <img src="<?= $assets; ?>assets/images/users/user-1.jpg" alt="" class="w-full h-full rounded-full border-2 border-slate-100">
          </div>
        </a>
        <a href="#">
          <div>
            <span class=" relative cursor-pointer rounded-full text-[20px] flex flex-col items-center justify-center mb-1 dark:text-white
          text-slate-900">
        <iconify-icon icon="heroicons-outline:bell"></iconify-icon>
        <span class="absolute right-[17px] lg:hrefp-0 -hrefp-2 h-4 w-4 bg-red-500 text-[8px] font-semibold flex flex-col items-center
            justify-center rounded-full text-white z-[99]">
          2
        </span>
            </span>
            <span class=" block text-[11px] text-slate-600 dark:text-slate-300">
        Notifications
      </span>
          </div>
        </a>
      </div>
    </div>
  </main>
  <!-- scripts -->
  <script src="<?= $assets; ?>assets/js/jquery-3.6.0.min.js"></script>
  <script src="<?= $assets; ?>assets/js/rt-plugins.js"></script>
  <script src="<?= $assets; ?>assets/js/app.js"></script>
</body>
</html>