<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
  
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>
  
  <xsl:template match="/">
    <html>
      <head>
        <title>XML Sitemap</title>
        <style type="text/css">
          body {
            font-family: Arial, sans-serif;
            padding: 40px;
            max-width: 1600px;
            margin: 0 auto;
          }
          table {
            width: 100%;
            margin-top: 40px;
          }
          th {
            background-color: #dee2e6;
            text-align: left;
            padding: 10px;
          }
          tr:nth-child(odd) {
            background-color: #f1f3f5;
          }
          td {
            padding: 12px;
            vertical-align: center;
            word-break: break-all;
          }
        </style>
      </head>
      <body>
        <h1>XML Sitemap</h1>
        <p>Total URLs in this sitemap: <xsl:value-of select="count(sitemap:urlset/sitemap:url)"/></p>
        <table>
          <tr>
            <th>URL</th>
            <xsl:if test="//image:image">
                <th>Image</th>
            </xsl:if>
            <xsl:if test="//sitemap:lastmod">
                <th>Last Modified</th>
            </xsl:if>
          </tr>
          <xsl:for-each select="sitemap:urlset/sitemap:url">
            <tr>
              <td>
                <a href="{sitemap:loc}" >
                  <xsl:value-of select="sitemap:loc"/>
                </a>
              </td>
   
              <xsl:if test="//image:image">
                <td>
                    <xsl:for-each select="image:image">
                        <a href="{image:loc}" target="_blank">
                            <xsl:value-of select="image:loc"/>
                        </a>
                    </xsl:for-each>
                </td>
              </xsl:if>

              <xsl:if test="//sitemap:lastmod">
                <td>
                    <xsl:if test="sitemap:lastmod">
                    <xsl:value-of select="substring(sitemap:lastmod, 1)"/>
                    </xsl:if>
                </td>
              </xsl:if>
            </tr>
          </xsl:for-each>
        </table>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>