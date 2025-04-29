<script>
  const { data } = $props();

  console.log("Post", data.graphqlData);
</script>

<data.template data={data.graphqlData} />
