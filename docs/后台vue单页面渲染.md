# 后台的vue单页面渲染

> 核心: 控制器只渲染页面视图,不进行逻辑判断,具体页面渲染逻辑交给vue-app 布局

## 三个元素
1. DcatAdmin的后台布局 admin::layouts.content
2. DcatAdmin的Pjax页面(无后台布局,只返回内容部分)
3. 内页,被iframe迁入的vue页面

## 三个模式
1. ?standalone=1 内页模式,没有布局就vue页面
2. pjax模式,没有后台布局,有iframe,内嵌内页
3. 普通模式,有后台布局 + iframe


## 
