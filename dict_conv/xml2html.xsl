<?xml version='1.0' encoding="koi8-r" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>
<xsl:output method="html" indent="yes" encoding="koi8-r"/>
<!--
	Преобразование xml файла в формат 'html',
-->

<!-- Преобразование названия части речи в формат dict -->
<xsl:template name="set_type">  
	<xsl:param name="key">0</xsl:param>	
	<xsl:variable name="nKey" select="normalize-space($key)"/>
	<xsl:choose>
		<xsl:when test="$nKey='noun'">
			<xsl:text>_n.</xsl:text>
		</xsl:when>
		<xsl:when test="$nKey='verb'">
			<xsl:text>_v.</xsl:text>
		</xsl:when>
		<xsl:when test="$nKey='adjective'">
			<xsl:text>_a.</xsl:text>
		</xsl:when>
		<xsl:when test="$nKey='adverb'">
			<xsl:text>_adv.</xsl:text>
		</xsl:when>
		<xsl:when test="$nKey='misuse'">
			<xsl:text>_mis.</xsl:text>
		</xsl:when>
		<xsl:otherwise> <!-- иначе просто выводим key -->
			<xsl:text>_</xsl:text><xsl:value-of select="$key"/><xsl:text>.</xsl:text>
		</xsl:otherwise>
  	</xsl:choose>					
</xsl:template>

<!-- Преобразование названия типа перевода -->
<xsl:template name="set_variant_type">  
	<xsl:param name="key">0</xsl:param>	
	<xsl:variable name="nKey" select="normalize-space($key)"/>
	<xsl:choose>
		<xsl:when test="$nKey='typography'">
			<xsl:text>_полигр.</xsl:text>
		</xsl:when>
		<xsl:when test="$nKey='depricated'">
			<xsl:text>_нерек.</xsl:text>
		</xsl:when>
		<xsl:when test="$nKey='jargon'">
			<xsl:text>_жаргон.</xsl:text>
		</xsl:when>
		<xsl:when test="$nKey='dialog'">
			<xsl:text>_интерф.</xsl:text>
		</xsl:when>
		<xsl:when test="$nKey='program'">
			<xsl:text>_прогр.</xsl:text>
		</xsl:when>
		<xsl:when test="$nKey='obsolete'">
			<xsl:text>_уст.</xsl:text>
		</xsl:when>
		<xsl:otherwise> <!-- иначе просто выводим key -->
			<xsl:text>_</xsl:text><xsl:value-of select="$key"/><xsl:text>.</xsl:text>
		</xsl:otherwise>
  	</xsl:choose>					
</xsl:template>


<xsl:template match="/">
<xsl:value-of select="dictionary/@name"/>{*}
<xsl:for-each select="dictionary/article">
<xsl:value-of select="@word"/>{|}
<p><dl><dt><strong><xsl:value-of select="@word"/></strong></dt>
<xsl:variable name="totalTypes" select="count(type)"/>
<xsl:for-each select="type">
	<xsl:call-template name="type">  
		<xsl:with-param name="num" select="$totalTypes"/>
	</xsl:call-template>
</xsl:for-each>
</dl>
</p>
<br />
<!-- перевод строки, пока делаю таким образом-->
<xsl:text>
{==}
</xsl:text>
</xsl:for-each>	
</xsl:template>

<!-- Разбор частей речи -->
<xsl:template name="type">  
	<xsl:param name="num">0</xsl:param>
	<dd>
	<b>
	<xsl:if test="$num>1">
		<xsl:number value="position()" format="1. "/>
	</xsl:if>
	<xsl:if test="not(string-length(normalize-space(@name))=0)">
		<!-- Преобразуем тип согласно списку типов -->
		<xsl:call-template name="set_type">
			<xsl:with-param name="key" select="@name"/>
		</xsl:call-template>
	</xsl:if>
	</b>
	</dd>
	<xsl:variable name="totalMeaning" select="count(meaning)"/>	
	<xsl:for-each select="meaning">
	<dd>
		<xsl:call-template name="meaning">
			<xsl:with-param name="num" select="$totalMeaning"/>
		</xsl:call-template>
	</dd>
	</xsl:for-each>
</xsl:template>

<!-- Разбор 'смысла' -->
<xsl:template name="meaning">  
	<xsl:param name="num">0</xsl:param>
	<xsl:if test="$num>1">
		<xsl:number value="position()" format="1> "/>
	</xsl:if>
	<xsl:for-each select="variant">
		<xsl:call-template name="variant"/>
	</xsl:for-each>
	
	<xsl:variable name="countLinks" select="count(link)"/>
	<xsl:if test="$countLinks>=1">
		<xsl:text>см. </xsl:text>
		<xsl:for-each select="link">	
			<xsl:call-template name="link"/>
			<xsl:if test="position()!=$countLinks">
				<xsl:text>, </xsl:text>
			</xsl:if>				
		</xsl:for-each>
	</xsl:if>
</xsl:template>

<!-- Разбор вариантов перевода -->
<xsl:template name="variant">  
<xsl:if test="not(string-length(normalize-space(@type))=0)">

	<!-- Преобразуем тип согласно списку типов -->
	<xsl:call-template name="set_variant_type">
		<xsl:with-param name="key" select="@type"/>
	</xsl:call-template>
	<xsl:text> </xsl:text> 
<!--  
	<xsl:value-of select="@type"/><xsl:text>. </xsl:text> 
--> 

</xsl:if>
<xsl:value-of select="."/><xsl:text>; </xsl:text>
</xsl:template>

<!-- ******************************************************************************* -->
<!-- Разбор ссылки -->
<xsl:template name="link">
<xsl:if test="not(string-length(normalize-space(.))=0)">
	<xsl:text>{link:</xsl:text><xsl:value-of select="."/><xsl:text>}</xsl:text>
</xsl:if>
</xsl:template>

</xsl:stylesheet>