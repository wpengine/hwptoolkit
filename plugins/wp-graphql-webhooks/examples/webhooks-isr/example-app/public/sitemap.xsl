<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
  xmlns="http://www.w3.org/1999/xhtml">
  
  <xsl:output method="html" encoding="UTF-8" indent="yes" />
  
  <xsl:template match="/">
    <html>
      <head>
        <title>XML Sitemap</title>
        <style>
          body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
          }
          table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
          }
          th, td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: left;
          }
          th {
            background-color: #f2f2f2;
            font-weight: bold;
          }
          tr:nth-child(even) {
            background-color: #f9f9f9;
          }
          h1 {
            color: #2c3e50;
            margin-bottom: 20px;
          }
          a {
            color: #3498db;
            text-decoration: none;
          }
          a:hover {
            text-decoration: underline;
          }
          .info {
            margin-bottom: 10px;
          }
        </style>
      </head>
      <body>
        <!-- Handle sitemapindex -->
        <xsl:if test="sitemap:sitemapindex">
          <h1>XML Sitemap Index</h1>
          <div class="info">
            Total Sitemaps: <xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)" />
          </div>
          <table>
            <tr>
              <th>Sitemap</th>
              <th>Last Modified</th>
            </tr>
            <xsl:for-each select="sitemap:sitemapindex/sitemap:sitemap">
              <tr>
                <td>
                  <!-- Use the data-real-url attribute if it exists, otherwise use the loc value -->
                  <a>
                    <xsl:attribute name="href">
                      <xsl:choose>
                        <xsl:when test="sitemap:loc/@data-real-url">
                          <xsl:value-of select="sitemap:loc/@data-real-url" />
                        </xsl:when>
                        <xsl:otherwise>
                          <xsl:value-of select="sitemap:loc" />
                        </xsl:otherwise>
                      </xsl:choose>
                    </xsl:attribute>
                    <xsl:value-of select="sitemap:loc" />
                  </a>
                </td>
                <td>
                  <xsl:value-of select="sitemap:lastmod" />
                </td>
              </tr>
            </xsl:for-each>
          </table>
        </xsl:if>
        
        <!-- Handle urlset -->
        <xsl:if test="sitemap:urlset">
          <h1>XML Sitemap</h1>
          <div class="info">
            Total URLs: <xsl:value-of select="count(sitemap:urlset/sitemap:url)" />
          </div>
          <table>
            <tr>
              <th>URL</th>
              <th>Last Modified</th>
            </tr>
            <xsl:for-each select="sitemap:urlset/sitemap:url">
              <tr>
                <td>
                  <a href="{sitemap:loc}">
                    <xsl:value-of select="sitemap:loc" />
                  </a>
                </td>
                <td>
                  <xsl:value-of select="sitemap:lastmod" />
                </td>
              </tr>
            </xsl:for-each>
          </table>
        </xsl:if>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>