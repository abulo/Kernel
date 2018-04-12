# EsParser
php的操作类库，通过写sql来转化dsl来查询elasticsearch
### composer使用
```php
	$sql = 'select * from alp_dish_sales_saas where sid in(994,290) limit 1,10';
	$sql='update alp_dish_sales_saas set mid=3  where adsid=15125110';
	$sql='delete from alp_dish_sales_saas where adsid=15546509';
    $sql="select *,concat_ws('_',category_name.keyword,dish_name.keyword,sku_name.keyword) as dfg from alp_dish_sales_saas where sale_date>'2017-01-01' and sale_date<'2017-09-02' group by dfg order by total_count desc";

    $sql = 'select *,DATE_FORMAT(sale_date,"%Y-%m-%d") as days from alp_dish_sales_saas group by days ';

	$es_config=array(
        	'index' =>"alp_dish_sales_saas",
        	'type'  =>"alp_dish_sales_saas",
        	'url'   =>"http://127.0.0.1:9200",
            'version' =>"5.x" //1.x 2.x 5.x 6.x,可以不配置，系统会请求获取版本，这样会多一次请求,建议配置一下
    	);
	$parser = new EsParser($sql, true,$es_config);//第三个参数是es的配置参数，一定要配置
	print_r($parser->result);//打印结果
	//print_r($parser->explain()); //打印dsl
```
### 目前支持的sql函数
    *  SQL Select
    *  SQL Delete
    *  SQL Update
    *  SQL Where
    *  SQL Order By
    *  SQL Group By
    *  SQL AND
    *  SQL OR (多重or如:((a=1 and b=2) or (c=3 and d=4)) and e=5)
    *  SQL Like
    *  SQL Not Like
    *  SQL COUNT distinct
    *  SQL In
    *  SQL Not In
    *  SQL =
    *  SQL !=
    *  SQL avg()
    *  SQL count()
    *  SQL max()
    *  SQL min()
    *  SQL sum()
    *  SQL Between
    *  SQL Aliases
    *  SQL concat_ws
    *  SQL DATE_FORMATE
    *  SQL Having
### 使用注意事项
    请在配置项填写es的版本,这样系统不会请求获取版本，这样不会多一次请求,建议配置一下
