<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#app-navbar-collapse">
                <span class="sr-only">Toggle Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="{{ url('/') }}">
                Laravel Shop
            </a>
        </div>
        <div class="collapse navbar-collapse" id="app-navbar-collapse">
            {{--顶部类目菜单开始--}}
            <ul class="nav navbar-nav">
                {{--判断模板是否有$categoryTre变量--}}
                @if(isset($categoryTree))
                    <li>
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            所有类目<b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu multi-level">
                            {{--遍历$categoryTree集合，将集合中的每一项以$category变量注入layouts._category_item模板中并渲染--}}
                            @each('layouts._category_item',$categoryTree,'category')
                        </ul>
                    </li>
                @endif
            </ul>
            {{--顶部类目菜单结束--}}
            <ul class="nav navbar-nav navbar-right">
                <!-- 登录注册链接开始 -->
                @guest
                    <li><a href=" {{ route('login') }} ">登录</a></li>
                    <li><a href=" {{ route('register') }} ">注册</a></li>
                @else
                    <li>
                        <a href="{{route('cart.index')}}">
                            <span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span>
                        </a>
                    </li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        <span class="user-avatar pull-left" style="margin-right:8px; margin-top:-5px;">
                            <img src="{{asset('uploads\images\25235235235235345345.jpg')}}"
                                 class="img-responsive img-circle" width="30px" height="30px">
                        </span>
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a href="{{route('user_addresses.index')}}">收货地址</a>
                            </li>
                            <li>
                                <a href="{{route('orders.index')}}">我的订单</a>
                            </li>
                            <li>
                                <a href="{{route('installments.index')}}">分期付款</a>
                            </li>
                            <li>
                                <a href="{{route('products.favorites')}}">我的收藏</a>
                            </li>

                            <li>
                                <a href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                                    退出登录
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    {{ csrf_field() }}
                                </form>
                            </li>
                        </ul>
                    </li>
            @endguest
            <!-- 登录注册链接结束 -->
            </ul>
        </div>
    </div>
</nav>
