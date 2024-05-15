import { test, is, ok, run } from 'testra'
import { getServer } from '../../framework/env'

export default run(async () => {
  const {
    request,
    wpx,
    setSiteTemplate,
    resetSiteTemplate,
  } = await getServer()

  test('Taxonomy archive', async () => {
    const numTerms = 3
    let result = await wpx`
$term_ids = [];
for ($i = 1; $i <= ${numTerms}; $i++) {
  $term_ids []= wp_insert_term('Cat ' . $i, 'category', [ 'slug' => 'cat-' . $i ]);
}
return $term_ids;
`

    is(true, Array.isArray(result), 'create taxonomy terms returns array')
    is(numTerms, result.length, numTerms + ' terms created')

    const termIds = (result as any[]).reduce((ids, obj) => {
      ids.push(obj.term_id)
      return ids
    }, [])

    for (let i = 1, len = termIds.length; i <= len; i++) {
      const termId: number = termIds[i - 1]

      setSiteTemplate(`<Loop field=archive_term><Field id /></Loop>`)

      result = await request({
        method: 'GET',
        format: 'text',
        route: `/category/cat-${i}`,
      })

      is(
        termId,
        parseInt(result, 10),
        `category archive cat-${i} - ID ${termId}`,
      )

      const postIds: number[] = []
      const postTitles: string[] = []
      const numPosts = 3

      for (let j=1; j <= numPosts; j++) {
        const postTitle = `cat-${i}-post-${j}`
        const postId = await wpx`
        return wp_insert_post([
          'post_type' => 'post',
          'post_status' => 'publish',
          'post_title' => '${postTitle}',
          'post_content' => '',
          'post_excerpt' => '',
          'post_category' => [${termId}]
        ]);`
  
        is('number', typeof postId, `create ${postTitle} returns ID`)

        postTitles.push(postTitle)
        postIds.push(parseInt(postId, 10))
      }

      result = await wpx`return get_posts([
        'post_type' => 'post',
        'category' => ${termId},
        'fields' => 'ids'
      ]);`

      is(true, Array.isArray(result), 'can get posts of this category')
      is(numPosts, result.length, 'can get correct number of posts of this category')

      is(postIds.sort(), result.sort(), 'get created posts')

      const postTitleList = postTitles.join(',')

      /**
       * Category archive template
       */
      setSiteTemplate(`
<Loop type=post taxonomy=category terms="${termId}"><Field title /><If not last>,</If></Loop>
`)

      result = (
        await request({
          method: 'GET',
          format: 'text',
          route: `/category/cat-${i}`,
        })
      ).trim()

      is(postTitleList, result, 'category archive returns post titles')

      /**
       * Category archive template with taxonomy=current
       */
      setSiteTemplate(`
<Loop type=post taxonomy=current terms="${termId}"><Field title /><If not last>,</If></Loop>
`)

      result = (
        await request({
          method: 'GET',
          format: 'text',
          route: `/category/cat-${i}`,
        })
      ).trim()

      is(postTitleList, result, 'category archive template with taxonomy=current returns post title')

      /**
       * Category archive template with taxonomy=category terms=current
       */
      setSiteTemplate(`
<Loop type=post taxonomy=category terms=current><Field title /><If not last>,</If></Loop>
`)

      result = (
        await request({
          method: 'GET',
          format: 'text',
          route: `/category/cat-${i}`,
        })
      ).trim()

      is(postTitleList, result, 'category archive template with taxonomy=category terms=current returns post title')

      /**
       * Category archive template with taxonomy=current terms=current
       */
      setSiteTemplate(`
<Loop type=post taxonomy=current terms=current><Field title /><If not last>,</If></Loop>
`)

      result = (
        await request({
          method: 'GET',
          format: 'text',
          route: `/category/cat-${i}`,
        })
      ).trim()

      is(postTitleList, result, 'category archive template with taxonomy=current terms=current returns post title')

    }

    resetSiteTemplate()
  })

})
