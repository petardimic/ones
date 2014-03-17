/**
 * 通用视图
 * 
 * diplayGrid 封装 ng-grid 
 * */

var CommonView = {
    
    defaultTemplate : [
        ['CommonView/grid.html', '<div class="gridStyle" ng-grid="gridOptions"></div>']
    ],
    
    displyGrid: function($scope, resource, columnDefs, options) {
        
        $scope.totalServerItems = 0;
        
        options = options ? options : {};
        columnDefs = columnDefs ? columnDefs : null;
        
        /**
         * 分页/过滤器默认项
         * */
        var pagingOptions = {
            pageSizes: [15, 30, 50],
            pageSize: 15,
            currentPage: 1
        };
        var filterOptions = {
            filterText: "",
            useExternalFilter: true
        };
        
        //默认选项
        var defaults = {
            data: 'itemsList',
            enablePaging: true,
            showFooter: true,
            showFilter: true,
            showColumnMenu: true,
            showSelectionCheckbox: true,
            rowHeight: 40,
            headerRowHeight: 40,
            checkboxCellTemplate: '<div class="ngSelectionCell"><label><input tabindex="-1" class="ngSelectionCheckbox ace" type="checkbox" ng-checked="row.selected" /><span class="lbl"></span></label></div>',
            checkboxHeaderTemplate: '<div class="ngSelectionCell"><label><input class="ngSelectionHeader ace" type="checkbox" ng-show="multiSelect" ng-model="allSelected" ng-change="toggleSelectAll(allSelected)"/><span class="lbl"></span></label></div>',
            totalServerItems: 'totalServerItems',
            i18n: "zh-cn"
        };
        
        var opts = $.extend(defaults, options);
        
        opts.columnDefs = columnDefs;
        
        var setPagingData = function(data, page, pageSize) {
            var pagedData = data.slice((page - 1) * pageSize, page * pageSize);
            $scope.itemsList = pagedData;
            $scope.totalServerItems = data.length;
            if (!$scope.$$phase) {
                $scope.$apply();
            }
        };
        
        var getPagedDataAsync = function(pageSize, page, searchText) {
            setTimeout(function() {
                var data;

                if (searchText) {
                    var ft = searchText.toLowerCase();
                    resource.query(function(largeLoad) {
                        data = largeLoad.filter(function(item) {
                            return JSON.stringify(item).toLowerCase().indexOf(ft) != -1;
                        });
                        setPagingData(data, page, pageSize);
                    });
                } else {
                    resource.query(function(largeLoad) {
                        setPagingData(largeLoad, page, pageSize);
                    });
                }

            }, 100);
        };
        
        if(!('pagingOptions' in opts)) {
            $scope.pagingOptions = opts.pagingOptions = pagingOptions;
        }
        if(!('filterOptions' in opts)) {
            $scope.filterOptions = opts.filterOptions = filterOptions;
        }
        
        getPagedDataAsync(pagingOptions.pageSize, pagingOptions.currentPage);

        /**
         * 监视器
         * */
        $scope.$watch('pagingOptions', function(newVal, oldVal) {
            if (newVal !== oldVal) {
                getPagedDataAsync(pagingOptions.pageSize, pagingOptions.currentPage, filterOptions.filterText);
            }
        }, true);
        $scope.$watch('filterOptions', function(newVal, oldVal) {
            if (newVal !== oldVal) {
                getPagedDataAsync(pagingOptions.pageSize, pagingOptions.currentPage, filterOptions.filterText);
            }
        }, true);

        
        $scope.gridOptions = opts;

    }

}