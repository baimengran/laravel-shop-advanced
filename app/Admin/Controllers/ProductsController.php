<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ProductsController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('商品列表')
            //->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('商品编辑')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('创建商品')
            //->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product);

        $grid->id('Id')->sortable();
        $grid->title('商品名称');
        //$grid->description('Description');
        //$grid->image('Image');
        $grid->on_sale('已上架')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->price('价格');
        $grid->rating('评分');
        $grid->sold_count('销量');
        $grid->review_count('评论数');

//        $grid->created_at('Created at');
////        $grid->updated_at('Updated at');
        //修改动作
        $grid->actions(function ($actions) {
            //不在每一行后展示查看
            $actions->disableView();
            //不在每一行后展示删除
            $actions->disableDelete();
        });
        //批量工具
        $grid->tools(function ($tools) {
            //禁用批量删除按钮
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Product::findOrFail($id));

        $show->id('Id');
        $show->title('Title');
        $show->description('Description');
        $show->image('Image');
        $show->on_sale('On sale');
        $show->rating('Rating');
        $show->sold_count('Sold count');
        $show->review_count('Review count');
        $show->price('Price');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        //创建一个表单
        $form = new Form(new Product);

        //创建一个输入框，第一个参数title是模型的字段名，第二个参数是该字段描述
        $form->text('title', '商品名称')->rules('required');
        //$form->textarea('description', 'Description');
        //创建选择图片框,生成MD5随机文件名，并修改上传目录
        $form->image('image', '封面图片')->uniqueName()->move('public/upload/products');
        //创建选择图片框
        // $form->image('image', '封面图片')->rules('required|image');
        //创建一个富文本编辑器,默认laravel->admin禁用了editor表单组件，可在app/Admin/bootstrap。php内将‘editor’删除
        $form->editor('description', '商品描述')->rules('required');
        //创建一个单选框
        $form->radio('on_sale', '上架')->options(['1' => '是', '0' => '否'])->default('0');
        //$form->switch('on_sale', 'On sale')->default(1);
//        $form->decimal('rating', 'Rating')->default(5.00);
//        $form->number('sold_count', 'Sold count');
//        $form->number('review_count', 'Review count');
//        $form->decimal('price', 'Price');
        //添加一对多关联模型，关联SKU信息
        $form->hasMany('skus', 'SKU列表', function (Form\NestedForm $form) {
            $form->text('title', 'SKU 名称')->rules('required');
            $form->text('description', 'SKU 描述')->rules('required');
            $form->text('price', '单价')->rules('required|numeric|min:0.01');
            $form->text('stock', '剩余库存')->rules('required|integer|min:0');
        });

        //定义事件回调，当模型即将保存时会触发这个回调我们需要在保存商品之前拿到所有 SKU 中最低的价格作为商品的价格，然后通过 $form->model()->price 存入到商品模型中。
        $form->saving(function (Form $form) {
            //collect() 函数是 Laravel 提供的一个辅助函数，可以快速创建一个 Collection 对象。把用户提交上来的 SKU 数据放到 Collection 中，
            //利用 Collection 提供的 min() 方法求出所有 SKU 中最小的 price，后面的 ?: 0 则是保证当 SKU 数据为空时 price 字段被赋值 0。
            $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price') ?: 0;
        });

        return $form;
    }
}
