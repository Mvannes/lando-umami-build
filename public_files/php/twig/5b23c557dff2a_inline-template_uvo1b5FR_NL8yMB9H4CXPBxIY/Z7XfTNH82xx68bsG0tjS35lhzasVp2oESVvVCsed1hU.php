<?php

/* {# inline_template_start #}<h2 class="footer-promo__title">{{ promo_title }}</h2><p class="footer-promo__text">{{ promo_text }} {% if findmore_url %} <a href="{{ findmore_url }}">{{ findmore_text }}</a> {% endif %}</p> */
class __TwigTemplate_1f9c428bdbe038ca0306408c78c63378eddfd2a387cec3b7aa562fd123baa4cb extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $tags = array("if" => 1);
        $filters = array();
        $functions = array();

        try {
            $this->env->getExtension('Twig_Extension_Sandbox')->checkSecurity(
                array('if'),
                array(),
                array()
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 1
        echo "<h2 class=\"footer-promo__title\">";
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["promo_title"] ?? null), "html", null, true));
        echo "</h2><p class=\"footer-promo__text\">";
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["promo_text"] ?? null), "html", null, true));
        echo " ";
        if (($context["findmore_url"] ?? null)) {
            echo " <a href=\"";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["findmore_url"] ?? null), "html", null, true));
            echo "\">";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, ($context["findmore_text"] ?? null), "html", null, true));
            echo "</a> ";
        }
        echo "</p>";
    }

    public function getTemplateName()
    {
        return "{# inline_template_start #}<h2 class=\"footer-promo__title\">{{ promo_title }}</h2><p class=\"footer-promo__text\">{{ promo_text }} {% if findmore_url %} <a href=\"{{ findmore_url }}\">{{ findmore_text }}</a> {% endif %}</p>";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  43 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "{# inline_template_start #}<h2 class=\"footer-promo__title\">{{ promo_title }}</h2><p class=\"footer-promo__text\">{{ promo_text }} {% if findmore_url %} <a href=\"{{ findmore_url }}\">{{ findmore_text }}</a> {% endif %}</p>", "");
    }
}
