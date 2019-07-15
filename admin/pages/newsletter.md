---
title: Newsletter
'@extends':
  type: default
  context: blueprints://pages

form:
  validation: loose
  action: /subscribe.json
    
  fields:
    tabs:
      type: tabs
      active: 1

      fields:
        content:
          type: tab
          title: 'Audience'
          underline: false

          fields:
            audience:
              type: audience

        campaigns:
          type: tab
          title: 'Campaigns'

          fields:

            publishing:
              type: section
              title: 'Campaigns'
              underline: true
        templates:
          type: tab
          title: 'Templates'

          fields:

            publishing:
              type: section
              title: 'Newsletter Templates'
              underline: true
---
