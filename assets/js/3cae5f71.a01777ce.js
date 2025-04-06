"use strict";(self.webpackChunkwebsite=self.webpackChunkwebsite||[]).push([[354],{8453:(e,t,n)=>{n.d(t,{R:()=>a,x:()=>o});var s=n(6540);const i={},r=s.createContext(i);function a(e){const t=s.useContext(r);return s.useMemo((function(){return"function"==typeof e?e(t):{...t,...e}}),[t,e])}function o(e){let t;return t=e.disableParentContext?"function"==typeof e.components?e.components(i):e.components||i:a(e.components),s.createElement(r.Provider,{value:t},e.children)}},9483:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>c,contentTitle:()=>o,default:()=>p,frontMatter:()=>a,metadata:()=>s,toc:()=>l});const s=JSON.parse('{"id":"guides/entities","title":"Entities","description":"Orkestra AbstractEntities are simple data objects that represent your application\'s data structures.","source":"@site/docs/guides/entities.md","sourceDirName":"guides","slug":"/guides/entities","permalink":"/docs/guides/entities","draft":false,"unlisted":false,"editUrl":"https://github.com/Luc-cpl/orkestra/tree/main/docs/docs/guides/entities.md","tags":[],"version":"current","sidebarPosition":3,"frontMatter":{"sidebar_position":3},"sidebar":"tutorialSidebar","previous":{"title":"Service Providers","permalink":"/docs/guides/providers"},"next":{"title":"Service Decoration","permalink":"/docs/guides/service-decoration"}}');var i=n(4848),r=n(8453);const a={sidebar_position:3},o="Entities",c={},l=[{value:"Basic Entity",id:"basic-entity",level:2},{value:"Entity Factory",id:"entity-factory",level:2},{value:"Entity Attributes",id:"entity-attributes",level:2},{value:"Property Hooks (PHP 8.4+)",id:"property-hooks-php-84",level:2},{value:"Best Practices",id:"best-practices",level:2}];function d(e){const t={code:"code",h1:"h1",h2:"h2",header:"header",li:"li",ol:"ol",p:"p",pre:"pre",...(0,r.R)(),...e.components};return(0,i.jsxs)(i.Fragment,{children:[(0,i.jsx)(t.header,{children:(0,i.jsx)(t.h1,{id:"entities",children:"Entities"})}),"\n",(0,i.jsx)(t.p,{children:"Orkestra AbstractEntities are simple data objects that represent your application's data structures.\nThe initial idea is to cerate a behavior as Property Hooks and Asymmetric Visibility before PHP 8.4 and to have a easy way to create new objects with the EntityFactory"}),"\n",(0,i.jsx)(t.h2,{id:"basic-entity",children:"Basic Entity"}),"\n",(0,i.jsx)(t.p,{children:"This entity below is a good example of a basic usage. The properties in protected visibility are public-read, allowing us to read and"}),"\n",(0,i.jsx)(t.pre,{children:(0,i.jsx)(t.code,{className:"language-php",children:"use Orkestra\\Entities\\AbstractEntity;\n\nclass User extends AbstractEntity\n{\n    public function __construct(\n        protected string $name,\n        protected string $email,\n        private string $password,\n    ) {}\n}\n\n$user = new User(\n    name: 'Joe Doe',\n    email: 'joe@email.com',\n    password: '12345',\n);\n\necho $user->name; // Joe Doe\necho $user->email; // joe@email.com\necho $user->password; // throws an exception\n\n// Changes the user name and email values\n$user->set(\n    name: 'Jane Doe',\n    email: 'jane@email.com',\n);\n"})}),"\n",(0,i.jsx)(t.h2,{id:"entity-factory",children:"Entity Factory"}),"\n",(0,i.jsx)(t.p,{children:"Entities are created using a factory:"}),"\n",(0,i.jsx)(t.pre,{children:(0,i.jsx)(t.code,{className:"language-php",children:"use Orkestra\\Services\\Http\\Factories\\EntityFactory;\n\nclass UserController extends EntityFactory\n{\n    public function __construct(\n        private EntityFactory $factory,\n    ) {\n        //\n    }\n\n    #[Entity(User::class)]\n    public function __invoke(ServerRequestInterface $request): User\n    {\n        // Return a new user or throws BadRequestException in the middleware stage according validations\n        return $this->factory->make(User::class, $request->getParsedBody());\n    }\n}\n"})}),"\n",(0,i.jsx)(t.h2,{id:"entity-attributes",children:"Entity Attributes"}),"\n",(0,i.jsx)(t.p,{children:"You can define a repository and Faker values for tests:"}),"\n",(0,i.jsx)(t.pre,{children:(0,i.jsx)(t.code,{className:"language-php",children:"use Orkestra\\Services\\Http\\Attributes\\Entity;\nuse Orkestra\\Services\\Http\\Attributes\\Repository;\nuse Orkestra\\Services\\Http\\Attributes\\Faker;\n\n#[Entity]\n#[Repository(UserRepository::class)]\nclass User\n{\n    #[Faker('name')]\n    private string $name;\n\n    #[Faker('email')]\n    private string $email;\n\n    #[Faker('password')]\n    private string $password;\n\n    #[Faker('dateTime')]\n    private DateTimeImmutable $created_at;\n}\n"})}),"\n",(0,i.jsx)(t.h2,{id:"property-hooks-php-84",children:"Property Hooks (PHP 8.4+)"}),"\n",(0,i.jsx)(t.p,{children:"Orkestra recommends using native property hooks instead of our AbstractEntity since PHP 8.4."}),"\n",(0,i.jsx)(t.pre,{children:(0,i.jsx)(t.code,{className:"language-php",children:"class User\n{\n    public function __construct(\n        public string $name;\n        public string $email;\n        private(set) string $password;\n    ) {}\n}\n"})}),"\n",(0,i.jsx)(t.h2,{id:"best-practices",children:"Best Practices"}),"\n",(0,i.jsxs)(t.ol,{children:["\n",(0,i.jsx)(t.li,{children:"Keep entities focused and simple"}),"\n",(0,i.jsx)(t.li,{children:"Use EntityFactory for entity creation"}),"\n",(0,i.jsx)(t.li,{children:"Set entities Attributes to define middleware validation, repository and faker values"}),"\n",(0,i.jsx)(t.li,{children:"Validate data in constructors"}),"\n",(0,i.jsx)(t.li,{children:"Use events for side effects"}),"\n",(0,i.jsx)(t.li,{children:"Document entity properties"}),"\n",(0,i.jsx)(t.li,{children:"Use type hints"}),"\n",(0,i.jsx)(t.li,{children:"Follow immutability when possible"}),"\n"]})]})}function p(e={}){const{wrapper:t}={...(0,r.R)(),...e.components};return t?(0,i.jsx)(t,{...e,children:(0,i.jsx)(d,{...e})}):d(e)}}}]);