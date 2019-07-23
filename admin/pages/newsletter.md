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

        campaign:
          type: tab
          title: 'Campaigns'
          underline: false
 
          fields:
            campaign:
              type: campaign
---
