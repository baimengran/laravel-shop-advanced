<?php

namespace App\Admin\Controllers;

use App\Http\Requests\Request;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CategoriesController extends Controller
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
            ->header('商品类目列表')
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
            ->header('编辑商品类目')
            //->description('description')
            ->body($this->form(true)->edit($id));
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
            ->header('创建商品类目')
            //->description('description')
            ->body($this->form(false));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Category);

        $grid->id('Id')->sortable();
        $grid->name('名称');
        //$grid->parent_id('Parent id');
        $grid->level('层级');
        $grid->is_directory('是否目录')->display(function ($value) {
            return $value ? '是' : '否';
        });
        $grid->path('类目路径');
        //$grid->created_at('Created at');
        //$grid->updated_at('Updated at');
        $grid->actions(function ($action) {
            //不展示laravel-Admin默认的查看按钮
            $action->disableView();
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
        $show = new Show(Category::findOrFail($id));

        $show->id('Id');
        $show->name('Name');
        $show->parent_id('Parent id');
        $show->is_directory('Is directory');
        $show->level('Level');
        $show->path('Path');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($isEditing = false)
    {
        $form = new Form(new Category);

        $form->text('name', '类目名称')->rules('required');

        //如果不是编辑的情况
        if ($isEditing) {
            //不允许修改【是否目录】和【父类目】字段的值
            //用display()方法来展示值，with()方法接收一个匿名函数，会吧字段值传给匿名函数并把返回值展示出来
            $form->display('is_directory', '是否目录')->with(function ($value) {
                return $value ? '是' : '否';
            });
            //支持用符号 . 来展示关联关系的字段
            $form->display('parent.name', '父类目');
        } else {
            //定义一个名为【是否目录】的单选框
            $form->radio('is_directory', '是否目录')
                ->options(['1' => '是', '0' => '否'])
                ->default('0')
                ->rules('required');

            //定义一个名为父类目的下拉框
            $form->select('parent_id', '父类目')->ajax('/admin/api/categories');
        }


//        $form->number('parent_id', 'Parent id');
//        $form->switch('is_directory', 'Is directory');
//        $form->number('level', 'Level');
//        $form->text('path', 'Path');

        return $form;
    }

    /**
     * 定义父类目下拉搜索接口
     * @param Request $request
     */
    public function apiIndex(Request $request)
    {
        //dd(1);
        //用户输入的值通过q参数获取
        $search = $request->input('q');
        $result = Category::query()//boolval()方法转换成boolean
            ->where('is_directory', boolval($request->input('is_directory',true)))
            ->where('name', 'like', '%'.$search . '%')
            ->paginate();

        //把查询出来的结果重新组装成laravel-admin需要的格式
        //getCollection 获取到这个分页里的数据集合，setCollection 替换分页的数据
        $result->setCollection($result->getCollection()->map(function (Category $category) {
            return ['id' => $category->id, 'text' => $category->full_name];
        }));


        return $result;
    }

}
